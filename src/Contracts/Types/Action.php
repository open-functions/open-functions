<?php

namespace OpenFunctions\Core\Contracts\Types;

use OpenFunctions\Core\Types\Computer\ActionClick;
use OpenFunctions\Core\Types\Computer\ActionDoubleClick;
use OpenFunctions\Core\Types\Computer\ActionDrag;
use OpenFunctions\Core\Types\Computer\ActionKeypress;
use OpenFunctions\Core\Types\Computer\ActionMove;
use OpenFunctions\Core\Types\Computer\ActionScreenshot;
use OpenFunctions\Core\Types\Computer\ActionScroll;
use OpenFunctions\Core\Types\Computer\ActionType;
use OpenFunctions\Core\Types\Computer\ActionWait;

abstract class Action
{
    /**
     * Factory method to create an action instance based on the "type" in the data.
     *
     * @param array $data The input data containing the "type" field.
     * @return object One of the action instances (ActionClick, ActionDoubleClick, etc.).
     * @throws \Exception If the action type is unknown.
     */
    public static function fromArray(array $data): object {
        $actionType = $data['type'] ?? '';
        return match ($actionType) {
            'click'         => ActionClick::fromArray($data),
            'double_click'  => ActionDoubleClick::fromArray($data),
            'drag'          => ActionDrag::fromArray($data),
            'keypress'      => ActionKeypress::fromArray($data),
            'move'          => ActionMove::fromArray($data),
            'screenshot'    => ActionScreenshot::fromArray($data),
            'scroll'        => ActionScroll::fromArray($data),
            'type'          => ActionType::fromArray($data),
            'wait'          => ActionWait::fromArray($data),
            default         => throw new \Exception('Unknown action type: ' . $actionType),
        };
    }

    abstract public function toArray(): array;
}