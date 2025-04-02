<?php

namespace OpenFunctions\Core\Contracts\Orchestration;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;

/**
 * A no-operation processor that implements all methods with empty bodies.
 */
abstract class AbstractRunController extends AbstractEventProcessor
{
    abstract public function shouldLoopStop(int $iteration, LoopInterface $loop, ProviderResponse $response): bool;

    public function shouldCancelRun(LoopInterface $lastLoop): bool
    {
        return false;
    }
}