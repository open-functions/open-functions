<?php

namespace OpenFunctions\Core\Types\Computer;

use OpenFunctions\Core\Contracts\Types\Action;

class ActionDrag extends Action
{
    public string $type = 'drag';
    /** @var ActionDragPath[] */
    public array $path = [];

    public static function fromArray(array $data): self {
        $instance = new self();
        if (isset($data['path']) && is_array($data['path'])) {
            foreach ($data['path'] as $pathItem) {
                $instance->path[] = ActionDragPath::fromArray($pathItem);
            }
        }
        return $instance;
    }

    public function toArray(): array
    {
        $pathItems = [];
        foreach  ($this->path as $pathItem) {
            $pathItems[] = $pathItem->toArray();
        }

        return [
            'type' => $this->type,
            'path' => $pathItems,
        ];
    }
}