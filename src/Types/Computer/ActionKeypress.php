<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionKeypress extends Action
{
    public string $type = 'keypress';
    /** @var string[] */
    public array $keys = [];

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->keys = $data['keys'] ?? [];
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'keys' => $this->keys,
        ];
    }
}