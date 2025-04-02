<?php

namespace OpenFunctions\Core\List;

use OpenFunctions\Core\Contracts\Types\Item;

class ItemListGroup
{
    /**
     * @var string Group identifier.
     */
    protected string $identifier;

    /**
     * @var Item[] Items in this group.
     */
    protected array $items = [];

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function setItems(array $items): self
    {
        $this->items = [];

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Get the group identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Add an item to this group.
     *
     * @param Item $item
     * @return $this
     */
    public function addItem(Item $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Prepend an item to this group.
     *
     * @param Item $item
     * @return $this
     */
    public function prependItem(Item $item): self
    {
        array_unshift($this->items, $item);
        return $this;
    }

    /**
     * Get all items in this group.
     *
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Count the items in this group.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}