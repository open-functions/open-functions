<?php

namespace OpenFunctions\Core\List;

use OpenFunctions\Core\Contracts\List\ItemListFilterInterface;
use OpenFunctions\Core\Contracts\List\ItemListPresenter;
use OpenFunctions\Core\Contracts\Types\Item;

class ItemList
{
    /**
     * Default group identifier.
     */
    public const DEFAULT_GROUP_IDENTIFIER = 'default';

    /**
     * @var ItemListGroup[] Ordered array of groups.
     */
    protected array $groups = [];

    public function __construct(array $items = [])
    {
        $this->addItems($items);
    }

    /**
     * Add a single item to a specific group.
     *
     * @param Item $item The item to add.
     * @param string|null $groupIdentifier The group identifier.
     * @return $this
     */
    public function addItem(Item $item, ?string $groupIdentifier = null): self
    {
        if ($groupIdentifier) {
            $group = $this->getGroupByIdentifier($groupIdentifier);
        } else {
            $group = $this->createDefaultGroup();
        }

        $group->addItem($item);

        return $this;
    }

    /**
     * Add multiple items to a specific group.
     *
     * @param Item[] $items The items to add.
     * @param string|null $groupIdentifier The group identifier.
     * @return $this
     */
    public function addItems(array $items, ?string $groupIdentifier = null): self
    {
        foreach ($items as $item) {
            $this->addItem($item, $groupIdentifier);
        }
        return $this;
    }

    public function getItems(): array
    {
        return $this->flattenItems();
    }

    /**
     * Prepend a single item to a specific group.
     *
     * @param Item $item The item to prepend.
     * @param string|null $groupIdentifier The group identifier.
     * @return $this
     */
    public function prependItem(Item $item, ?string $groupIdentifier = null): self
    {
        if ($groupIdentifier) {
            $group = $this->getGroupByIdentifier($groupIdentifier);
        } else {
            $group = $this->groups[0];
        }

        $group->prependItem($item);

        return $this;
    }

    /**
     * Prepend multiple items to a specific group.
     *
     * @param Item[] $items The items to prepend.
     * @param string $groupIdentifier The group identifier (defaults to "default").
     * @return $this
     */
    public function prependItems(array $items, ?string $groupIdentifier = null): self
    {
        foreach ($items as $item) {
            $this->prependItem($item, $groupIdentifier);
        }
        return $this;
    }

    public function createDefaultGroup(): ItemListGroup
    {
        $newIdentifier = uniqid(self::DEFAULT_GROUP_IDENTIFIER . '_');
        $newGroup = new ItemListGroup($newIdentifier);
        $this->addGroup($newGroup);

        return $newGroup;
    }

    /**
     * Retrieve a group by its identifier. If no group is found, a new one is created.
     *
     * @param string $groupId
     * @return ItemListGroup
     */
    public function getGroupByIdentifier(string $groupId): ItemListGroup
    {
        foreach ($this->groups as $group) {
            if ($group->getIdentifier() === $groupId) {
                return $group;
            }
        }
        // If no group was found, create a new group with the provided identifier.
        $newGroup = new ItemListGroup($groupId);
        $this->addGroup($newGroup);

        return $newGroup;
    }

    /**
     * Add a custom group at the end.
     *
     * @param ItemListGroup $group
     * @return $this
     */
    public function addGroup(ItemListGroup $group): self
    {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * Prepend a custom group at the beginning.
     *
     * @param ItemListGroup $group
     * @return $this
     */
    public function prependGroup(ItemListGroup $group): self
    {
        array_unshift($this->groups, $group);
        return $this;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Flatten all items from all groups into a single array while preserving group order.
     *
     * @return Item[]
     */
    protected function flattenItems(): array
    {
        $allItems = [];
        foreach ($this->groups as $group) {
            $allItems = array_merge($allItems, $group->getItems());
        }
        return $allItems;
    }

    /**
     * Convert the list to an array for completions.
     *
     * @param ItemListPresenter[] $presenters Optional list of presenters to apply.
     * @param ItemListFilterInterface[] $filters Optional list of filters to apply.
     * @return array
     */
    public function asCompletion(array $presenters = [], array $filters = []): array
    {
        $clone = $this->transform($presenters, $filters);

        $allItems = $clone->flattenItems();

        return array_map(function (Item $item) {
            return $item->asCompletion();
        }, $allItems);
    }

    /**
     * Convert the list to an array for responses.
     *
     * @param ItemListPresenter[] $presenters Optional list of presenters to apply.
     * @param ItemListFilterInterface[] $filters Optional list of filters to apply.
     * @return array
     */
    public function asResponses(array $presenters = [], array $filters = []): array
    {
        $clone = $this->transform($presenters, $filters);

        $allItems = $clone->flattenItems();

        return array_map(function (Item $item) {
            return $item->asResponses();
        }, $allItems);
    }

    /**
     * Transforms the current list by applying the specified presenters and filters.
     *
     * This method creates a deep clone of the current ItemList instance and applies each provided
     * presenter and filter in order. The presenters can modify the list's presentation, while the filters
     * are used to refine or limit the list. The original list remains unmodified.
     *
     * @param ItemListPresenter[]       $presenters An optional array of presenters to modify the list.
     * @param ItemListFilterInterface[] $filters    An optional array of filters to refine the list.
     * @return ItemList A transformed clone of the original ItemList.
     */
    public function transform(array $presenters = [], array $filters = []): ItemList
    {
        $clone = clone $this;

        foreach ($presenters as $presenter) {
            $presenter->present($clone);
        }

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $clone = $filter->filter($clone);
            }
        }

        return $clone;
    }

    /**
     * Delete a group by its identifier.
     *
     * @param string $groupIdentifier The group identifier to delete.
     * @return $this
     */
    public function deleteGroupByIdentifier(string $groupIdentifier): self
    {
        foreach ($this->groups as $key => $group) {
            if ($group->getIdentifier() === $groupIdentifier) {
                unset($this->groups[$key]);
                // Reindex the array to preserve group order
                $this->groups = array_values($this->groups);
                break;
            }
        }
        return $this;
    }

    /**
     * Count all items across all groups.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->flattenItems());
    }

    /**
     * Magic method invoked when the object is cloned.
     * This ensures that each group is deep cloned.
     */
    public function __clone()
    {
        // Clone every group so that the new ItemList has independent groups.
        foreach ($this->groups as $key => $group) {
            $this->groups[$key] = clone $group;
        }
    }
}