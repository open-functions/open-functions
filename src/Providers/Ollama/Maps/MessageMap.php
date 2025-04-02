<?php

namespace OpenFunctions\Core\Providers\Ollama\Maps;

use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Contracts\Types\Message;
use OpenFunctions\Core\Types\AssistantMessage;
use OpenFunctions\Core\Types\DeveloperMessage;
use OpenFunctions\Core\Types\FunctionCall;
use OpenFunctions\Core\Types\FunctionCallOutput;
use OpenFunctions\Core\Types\Message\MessageContentImageUrl;
use OpenFunctions\Core\Types\SystemMessage;
use OpenFunctions\Core\Types\UserMessage;
use Exception;

class MessageMap
{
    public function mapItem(Item $item): array
    {
        return match ($item::class) {
            UserMessage::class => $this->mapUserMessage($item),
            AssistantMessage::class => $this->mapAssistantMessage($item),
            SystemMessage::class, DeveloperMessage::class => $this->mapSystemMessage($item),
            FunctionCall::class => $this->mapFunctionCall($item),
            FunctionCallOutput::class => $this->mapFunctionCallOutput($item),
            default => throw new Exception('Could not map message type ' . $item::class),
        };
    }

    public function mapUserMessage(UserMessage $message): array
    {
        $result = [
            'role' => 'user',
            'content' => $message->getText(),
            'images' => []
        ];

        foreach ($message->content as $content) {
            if ($content instanceof MessageContentImageUrl) {
                $result['images'][] = $content->asBase64();
            }
        }

        return $result;
    }

    public function mapAssistantMessage(AssistantMessage $message): array
    {
        return [
            'role' => 'assistant',
            'content' => $message->getText(),
        ];
    }

    public function mapSystemMessage(Message $message): array
    {
        return [
            'role' => 'system',
            'content' => $message->getText(),
        ];
    }

    public function mapFunctionCall(FunctionCall $functionCall): array
    {
        return [
            'role' => 'assistant',
            'content' => "",
            'tool_calls' => [
                [
                    "function" => [
                        "name" => $functionCall->functionName,
                        "arguments" => json_decode($functionCall->functionArguments, true)
                    ]
                ]
            ]
        ];
    }

    public function mapFunctionCallOutput(FunctionCallOutput $output): array
    {
        return [
            'role' => 'tool',
            'content' => $output->output,
        ];
    }
}