<?php

namespace OpenFunctions\Core\Contracts\Providers;

use OpenFunctions\Core\Contracts\AbstractOpenFunction;
use OpenFunctions\Core\List\ItemList;

/**
 * Interface ProviderInterface
 *
 * This interface defines a method to send a request to an LLM provider.
 */
interface ProviderInterface
{
    /**
     * Make a request to the LLM provider.
     *
     * @param array    $params         The parameters for the request.
     * @param ItemList $itemList       The list of items to work with.
     * @param AbstractOpenFunction|null $openFunction Optional AbstractOpenFunction to perform an open operation.
     * @param array $presenters Optional array of presenters (implementing ItemListPresenter).
     * @param array $filters        Optional array of filters (implementing ItemListFilterInterface).
     *
     * @return ProviderResponse Returns a provider response.
     */
    public function request(
        array $params,
        ItemList $itemList,
        ?AbstractOpenFunction $openFunction = null,
        array $presenters = [],
        array $filters = [],
    ): ProviderResponse;

    public function onBeforeRequest(callable $callback): void;
}