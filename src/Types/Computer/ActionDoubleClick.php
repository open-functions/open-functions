<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionDoubleClick extends Action
{
    public string $type = 'double_click';
    public int $x;
    public int $y;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->x = isset($data['x']) ? (int)$data['x'] : 0;
        $instance->y = isset($data['y']) ? (int)$data['y'] : 0;
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'x' => $this->x,
            'y' => $this->y,
        ];
    }
}