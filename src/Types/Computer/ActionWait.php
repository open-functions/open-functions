<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionWait extends Action
{
    public string $type = 'wait';

    public static function fromArray(array $data): self {
        // No additional properties for a wait action.
        return new self();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}