<?php

namespace OpenFunctions\Core\Contracts\Providers;

abstract class AbstractProvider implements ProviderInterface
{
    protected $beforeRequestCallback = null;

    /**
     * Registers a callback to be invoked before sending the request.
     *
     * The callback should expect two parameters:
     * - $rawItemList: the raw responses from the item list.
     * - $rawFunctionDefinition: the raw tool/function definitions.
     */
    public function onBeforeRequest(callable $callback): void
    {
        $this->beforeRequestCallback = $callback;
    }
}