<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionScroll extends Action
{
    public string $type = 'scroll';
    public int $scroll_x;
    public int $scroll_y;
    public int $x;
    public int $y;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->scroll_x = isset($data['scroll_x']) ? (int)$data['scroll_x'] : 0;
        $instance->scroll_y = isset($data['scroll_y']) ? (int)$data['scroll_y'] : 0;
        $instance->x = isset($data['x']) ? (int)$data['x'] : 0;
        $instance->y = isset($data['y']) ? (int)$data['y'] : 0;
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'scroll_x' => $this->scroll_x,
            'scroll_y' => $this->scroll_y,
            'x' => $this->x,
            'y' => $this->y,
        ];
    }
}