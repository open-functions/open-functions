<?php

namespace OpenFunctions\Core\Contracts\List;

use OpenFunctions\Core\List\ItemList;

interface ItemListPresenter
{
    /**
     * Extend the item list.
     *
     * @param ItemList $itemList The item list to extend.
     * @return void
     */
    public function present(ItemList $itemList): void;
}