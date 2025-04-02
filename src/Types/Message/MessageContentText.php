<?php

namespace OpenFunctions\Core\Types\Message;

use OpenFunctions\Core\Contracts\Types\MessageContent;

class MessageContentText implements MessageContent
{
    public string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function asCompletion(): array
    {
        return [
            'text' => $this->text,
            'type' => 'text',
        ];
    }

    public function asResponses(): array
    {
        return [
            'text' => $this->text,
            'type' => 'input_text',
        ];
    }
}