<?php

namespace OpenFunctions\Core\Providers;

use OpenFunctions\Core\Contracts\AbstractOpenFunction;
use OpenFunctions\Core\Contracts\Providers\AbstractProvider;
use OpenFunctions\Core\Contracts\Providers\ProviderInterface;
use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\List\ItemList;
use OpenFunctions\Core\Providers\Ollama\Maps\MessageMap;
use OpenFunctions\Core\Providers\Ollama\OllamaCompletionResponse;
use OpenFunctions\Core\Types\AssistantMessage;
use OpenFunctions\Core\Types\FunctionCall;
use GuzzleHttp\Client;


class OllamaCompletionProvider extends AbstractProvider
{
    protected Client $client;

    protected MessageMap $messageMap;

    /**
     * Constructor.
     *
     * @param string $baseUrl
     * @param array $headers
     */
    public function __construct(string $baseUrl, array $headers = [])
    {
        $params = [
            'base_uri' => $baseUrl,
        ];

        if ($headers) {
            $params['headers']  = $headers;
        }

        $this->client = new Client($params);
        $this->messageMap = new MessageMap();
    }

    /**
     * Sends a request to the OpenAI API using the provided parameters and item list.
     *
     * @param array                    $params         Additional parameters for the request (model, temperature, etc.).
     * @param ItemList                 $itemList       The list of items (messages) to include in the conversation.
     * @param AbstractOpenFunction|null $openFunction   Optional open function instance.
     * @param array                    $presenters     Optional presenters.
     * @param array                    $filters        Optional filters.
     *
     * @return ProviderResponse The provider response containing both the raw API response and the processed ItemList.
     */
    public function request(
        array $params,
        ItemList $itemList,
        ?AbstractOpenFunction $openFunction = null,
        array $presenters = [],
        array $filters = []
    ): ProviderResponse {
        // Convert the ItemList into an array of messages using each item's asCompletion() method.
        $itemList = $itemList->transform($presenters, $filters);
        $functionDefinitions = [];

        $messages = array_map(
            fn (Item $item) => $this->messageMap->mapItem($item),
            $itemList->getItems()
        );

        // Build the payload by merging the provided parameters with the messages.
        $payload = array_merge($params, ['messages' => $messages]);

        if ($openFunction !== null) {
            // Build the payload by merging the provided parameters with the tools.
            $payload = array_merge($payload, ['tools' => $openFunction->asCompletion()]);
            $functionDefinitions = $openFunction->asCompletion();
        }

        $payload['stream'] = false;

        // Trigger the before-request callback if set.
        if ($this->beforeRequestCallback) {
            call_user_func($this->beforeRequestCallback, $payload);
        }

        // Send the request to the OpenAI API using the chat completions endpoint.
        $response = $this->client->post('api/chat', [
            'json' => $payload
        ]);

        $rawResponse = json_decode($response->getBody()->getContents(), true);

        // Create a new ItemList to store the processed responses.
        $responseItemList = new ItemList();
        $finishReason = ProviderResponse::FINISH_REASON_UNKNOWN;

        if (isset($rawResponse['message'])) {
            $messageData = $rawResponse['message'];

            // If a function call is present in the message, map it to a FunctionCall instance.
            if (isset($messageData['tool_calls'])) {
                $finishReason = ProviderResponse::FINISH_REASON_TOOL_CALLS;
                foreach ($messageData['tool_calls'] as $toolCall) {
                    $functionCall = FunctionCall::make(
                        uniqid('tc_'),
                        $toolCall['function']['name'],
                        json_encode($toolCall['function']['arguments'])
                    );
                    $responseItemList->addItem($functionCall);
                }
            } else {
                $finishReason = ProviderResponse::FINISH_REASON_STOP;
                // Otherwise, create an AssistantMessage for the regular response.
                $assistantMessage = new AssistantMessage($messageData['content'] ?? '');
                $responseItemList->addItem($assistantMessage);
            }
        }

        // Return a ProviderResponseInterface implementation containing both the ItemList and the raw API response.
        return new OllamaCompletionResponse($messages, $functionDefinitions, $responseItemList, $finishReason, $rawResponse);
    }
}