<?php

namespace OpenFunctions\Core\Contracts;

use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Action;

abstract class AbstractComputerTool extends AbstractOpenFunction
{
    abstract public function callComputer(Action $action): ComputerResponseItem;
}