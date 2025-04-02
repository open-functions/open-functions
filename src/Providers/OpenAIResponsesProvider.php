<?php

namespace OpenFunctions\Core\Providers;

use OpenFunctions\Core\Contracts\AbstractOpenFunction;
use OpenFunctions\Core\Contracts\Providers\AbstractProvider;
use OpenFunctions\Core\Contracts\Providers\ProviderInterface;
use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\List\ItemList;
use OpenFunctions\Core\Providers\OpenAI\OpenAIResponsesResponse;
use OpenFunctions\Core\Types\AssistantMessage;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Types\FileSearchToolCall;
use OpenFunctions\Core\Types\FunctionCall;
use OpenFunctions\Core\Types\Reasoning;
use OpenFunctions\Core\Types\WebSearchToolCall;
use GuzzleHttp\Client as GuzzleClient;

class OpenAIResponsesProvider extends AbstractProvider
{
    protected GuzzleClient $client;

    /**
     * Constructor.
     *
     * @param string $apiKey Your OpenAI API key.
     */
    public function __construct(string $apiKey, string $url = 'https://api.openai.com/v1/')
    {
        // Initialize the OpenAI client with your API key
        $this->client = $this->getClient($apiKey, $url);
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
        $input = $itemList->asResponses($presenters, $filters);
        $functionDefinition = [];

        // Build the payload by merging the provided parameters with the messages.
        $payload = array_merge($params, ['input' => $input]);

        if ($openFunction !== null) {
            // Build the payload by merging the provided parameters with the tools.
            $payload = array_merge($payload, ['tools' => $openFunction->asResponses()]);
            $functionDefinition = $openFunction->asResponses();
        }

        if ($this->beforeRequestCallback) {
            call_user_func($this->beforeRequestCallback, $payload);
        }

        // Send the request to the OpenAI API using the chat completions endpoint.
        $httpResponse = $this->client->post('responses', [
            'json' => $payload
        ]);

        $rawResponse = json_decode($httpResponse->getBody()->getContents(), true);

        // Create a new ItemList to store the processed responses.
        $responseItemList = new ItemList();
        $finishReason = ProviderResponse::FINISH_REASON_UNKNOWN;

        if (isset($rawResponse['output'])) {
            foreach ($rawResponse['output'] as $item) {
                if ($item['type'] === 'message') {
                    if ($finishReason === ProviderResponse::FINISH_REASON_UNKNOWN) {
                        $finishReason = ProviderResponse::FINISH_REASON_STOP;
                    }

                    // Otherwise, create an AssistantMessage for the regular response.
                    $assistantMessage = AssistantMessage::fromResponsesArray($item);
                    $responseItemList->addItem($assistantMessage);
                } else if ($item['type'] === 'function_call') {
                    $finishReason = ProviderResponse::FINISH_REASON_TOOL_CALLS;

                    $functionCall = FunctionCall::fromResponsesArray($item);
                    $responseItemList->addItem($functionCall);
                } else if ($item['type'] === 'file_search_call') {
                    $fileSearchCall = FileSearchToolCall::fromResponsesArray($item);
                    $responseItemList->addItem($fileSearchCall);
                } else if ($item['type'] === 'web_search_call') {
                    $webSearchCall = WebSearchToolCall::fromResponsesArray($item);
                    $responseItemList->addItem($webSearchCall);
                } else if ($item['type'] === 'computer_call') {
                    $finishReason = ProviderResponse::FINISH_REASON_TOOL_CALLS;
                    $computerCall = ComputerCall::fromResponsesArray($item);
                    $responseItemList->addItem($computerCall);
                } else if ($item['type'] === 'reasoning') {
                    $reasoning = Reasoning::fromResponsesArray($item);
                    $responseItemList->addItem($reasoning);
                }
            }
        }

        // Return a ProviderResponseInterface implementation containing both the ItemList and the raw API response.
        return new OpenAIResponsesResponse($input, $functionDefinition, $responseItemList, $finishReason, $rawResponse);
    }

    private function getClient(string $apiKey, string $url): GuzzleClient
    {
        return new GuzzleClient([
            'base_uri' => $url,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ]
        ]);
    }
}