<?php

namespace OpenFunctions\Core\Orchestration\Models;

use OpenFunctions\Core\Contracts\AbstractOpenFunction;
use OpenFunctions\Core\Contracts\List\ItemListFilterInterface;
use OpenFunctions\Core\Contracts\List\ItemListPresenter;
use OpenFunctions\Core\Contracts\Orchestration\LoopInterface;
use OpenFunctions\Core\Contracts\Providers\ProviderInterface;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\List\ItemList;

class Loop implements LoopInterface
{
    protected string $identifier;
    protected ProviderInterface $provider;
    protected array $params;
    protected ItemList $itemList;
    protected ?AbstractOpenFunction $openFunction = null;

    /** @var ItemListFilterInterface[] */
    protected array $filters = [];
    /** @var ItemListPresenter[] */
    protected array $presenters = [];
    protected int $maxIterations = 5;
    protected ?ItemList $input = null;
    protected bool $clearItems = false;
    protected string $activeGroupIdentifier;

    /**
     * Constructor initializes the loop with required properties.
     */
    public function __construct(
        string $identifier,
        ProviderInterface $provider,
        array $params,
        ItemList $itemList,
        ?AbstractOpenFunction $openFunction = null
    ) {
        $this->identifier   = $identifier;
        $this->provider     = $provider;
        $this->params       = $params;
        $this->itemList     = $itemList;
        $this->openFunction = $openFunction;
    }

    // Getters only
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getItemList(): ItemList
    {
        return $this->itemList;
    }

    // Getters and setters
    public function getOpenFunction(): ?AbstractOpenFunction
    {
        return $this->openFunction;
    }

    public function setOpenFunction(?AbstractOpenFunction $openFunction): self
    {
        $this->openFunction = $openFunction;
        return $this;
    }

    /**
     * @return ItemListFilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return ItemListPresenter[]
     */
    public function getPresenters(): array
    {
        return $this->presenters;
    }

    public function setPresenters(array $presenters): self
    {
        $this->presenters = $presenters;
        return $this;
    }

    public function getMaxIterations(): int
    {
        return $this->maxIterations;
    }

    public function setMaxIterations(int $maxIterations): self
    {
        $this->maxIterations = $maxIterations;
        return $this;
    }

    public function getInput(): ?ItemList
    {
        return $this->input;
    }

    public function setInput(?ItemList $input): self
    {
        $this->input = $input;
        return $this;
    }

    public function getClearItems(): bool
    {
        return $this->clearItems;
    }

    public function clearItems(bool $clearItems): self
    {
        $this->clearItems = $clearItems;
        return $this;
    }

    // Loop control methods

    public function start(): void
    {
        // Initialize the active group identifier uniquely using the loop identifier
        $this->activeGroupIdentifier = uniqid(($this->identifier ?? 'emptyId') . '_');

        if ($this->input) {
            $this->itemList->addItems($this->input->getItems(), $this->activeGroupIdentifier);
        }
    }

    public function stop(): void
    {
        if ($this->clearItems) {
            $this->itemList->deleteGroupByIdentifier($this->activeGroupIdentifier);
        }
    }

    public function addItem(Item $item): self
    {
        $this->itemList->addItem($item, $this->activeGroupIdentifier);
        return $this;
    }
}