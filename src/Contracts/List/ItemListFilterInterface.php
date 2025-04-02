<?php

namespace OpenFunctions\Core\Contracts\List;

use OpenFunctions\Core\List\ItemList;

/**
 * Interface for filtering an item list.
 */
interface ItemListFilterInterface {
    /**
     * Filter the provided item list and return the modified item list.
     *
     * @param ItemList $itemList The full item list.
     * @return ItemList The filtered item list.
     */
    public function filter(ItemList $itemList): ItemList;
}