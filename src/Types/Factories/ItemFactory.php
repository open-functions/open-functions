<?php

namespace OpenFunctions\Core\Types\Factories;

use OpenFunctions\Core\Contracts\Types\Message;
use OpenFunctions\Core\Types\AssistantMessage;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Types\ComputerCallOutput;
use OpenFunctions\Core\Types\DeveloperMessage;
use OpenFunctions\Core\Types\FileSearchToolCall;
use OpenFunctions\Core\Types\FunctionCall;
use OpenFunctions\Core\Types\FunctionCallOutput;
use OpenFunctions\Core\Types\Reasoning;
use OpenFunctions\Core\Types\UserMessage;
use OpenFunctions\Core\Types\WebSearchToolCall;

class ItemFactory
{
    public static function listFromArray(array $itemList): array
    {
        $response = [];

        foreach ($itemList as $data) {
            $response[] = ItemFactory::listFromArray($data);
        }

        return $response;
    }

    public static function fromArray(array $data): object {
        $type = $data['type'] ?? '';

        switch ($type) {
            case 'message':
                // Check the role to determine if this message is an input or an output.
                if (isset($data['role']) && $data['role'] === 'assistant') {
                    return AssistantMessage::fromResponsesArray($data);
                } else if (isset($data['role']) && $data['role'] === 'developer') {
                    return DeveloperMessage::fromResponsesArray($data);
                } else {
                    return UserMessage::fromResponsesArray($data);
                }
            case 'function_call':
                return FunctionCall::fromResponsesArray($data);
            case 'function_call_output':
                return FunctionCallOutput::fromResponsesArray($data);
            case 'file_search_call':
                return FileSearchToolCall::fromResponsesArray($data);
            case 'web_search_call':
                return WebSearchToolCall::fromResponsesArray($data);
            case 'computer_call':
                return ComputerCall::fromResponsesArray($data);
            case 'computer_call_output':
                return ComputerCallOutput::fromResponsesArray($data);
            case 'reasoning':
                return Reasoning::fromResponsesArray($data);
            default:
                throw new \Exception("Unknown input item type: " . $type);
        }
    }
}