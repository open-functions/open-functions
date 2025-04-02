<?php

namespace OpenFunctions\Core\Providers;

use OpenFunctions\Core\Contracts\AbstractOpenFunction;
use OpenFunctions\Core\Contracts\Providers\AbstractProvider;
use OpenFunctions\Core\Contracts\Providers\ProviderInterface;
use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\List\ItemList;
use OpenFunctions\Core\Providers\OpenAI\Maps\FinishReasonMap;
use OpenFunctions\Core\Providers\OpenAI\OpenAICompletionResponse;
use OpenFunctions\Core\Types\AssistantMessage;
use OpenFunctions\Core\Types\FunctionCall;
use OpenAI;
use OpenAI\Client;

class OpenAICompletionProvider extends AbstractProvider
{
    protected Client $client;

    /**
     * Constructor.
     *
     * @param string $apiKey Your OpenAI API key.
     */
    public function __construct(string $apiKey, ?string $organization = null, ?string $project = null)
    {
        // Initialize the OpenAI client with your API key
        $this->client = OpenAI::client($apiKey, $organization, $project);
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
        $messages = $itemList->asCompletion($presenters, $filters);
        $functionDefinitions = [];

        // Build the payload by merging the provided parameters with the messages.
        $payload = array_merge($params, ['messages' => $messages]);

        if ($openFunction !== null) {
            // Build the payload by merging the provided parameters with the tools.
            $payload = array_merge($payload, ['tools' => $openFunction->asCompletion()]);
            $functionDefinitions = $openFunction->asCompletion();
        }

        // Trigger the before-request callback if set.
        if ($this->beforeRequestCallback) {
            call_user_func($this->beforeRequestCallback, $payload);
        }

        // Send the request to the OpenAI API using the chat completions endpoint.
        $rawResponse = $this->client->chat()->create($payload)->toArray();

        // Create a new ItemList to store the processed responses.
        $responseItemList = new ItemList();
        $finishReason = ProviderResponse::FINISH_REASON_UNKNOWN;

        if (isset($rawResponse['choices'])) {
            $choice = $rawResponse['choices'][0];

            $finishReason = FinishReasonMap::map($choice['finish_reason']);
            if (isset($choice['message'])) {
                $messageData = $choice['message'];

                // If a function call is present in the message, map it to a FunctionCall instance.
                if (isset($messageData['tool_calls'])) {
                    foreach ($messageData['tool_calls'] as $toolCall) {
                        $functionCall = FunctionCall::make(
                            $toolCall['id'],
                            $toolCall['function']['name'],
                            $toolCall['function']['arguments']
                        );
                        $responseItemList->addItem($functionCall);
                    }
                } else {
                    // Otherwise, create an AssistantMessage for the regular response.
                    $assistantMessage = new AssistantMessage($messageData['content'] ?? '');
                    $responseItemList->addItem($assistantMessage);
                }
            }
        }

        // Return a ProviderResponseInterface implementation containing both the ItemList and the raw API response.
        return new OpenAICompletionResponse($messages, $functionDefinitions, $responseItemList, $finishReason, $rawResponse);
    }
}