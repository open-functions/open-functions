<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionScreenshot extends Action
{
    public string $type = 'screenshot';

    public static function fromArray(array $data): self {
        // No extra properties for a screenshot action.
        return new self();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}