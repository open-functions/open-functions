<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionType extends Action
{
    public string $type = 'type';
    public string $text;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->text = $data['text'] ?? '';
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'text' => $this->text
        ];
    }
}