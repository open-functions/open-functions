<?php

namespace OpenFunctions\Core\Registry;

use OpenFunctions\Core\Contracts\List\ItemListPresenter;
use OpenFunctions\Core\List\ItemList;
use OpenFunctions\Core\Types\DeveloperMessage;

class RegistryPresenter implements ItemListPresenter
{
    /**
     * The registry
     *
     * @var OpenFunctionRegistry
     */
    private OpenFunctionRegistry $registry;

    /**
     * Intro message for the namespaces.
     *
     * @var string
     */
    public static string $namespaceIntro = 'Function are grouped by a prefix. Registered Groups are:';
    public static string $namespaceIntroMeta = 'Function are grouped by a prefix. You need to activate functions before using them. The following functions are available:';

    /**
     * Constructor.
     *
     * Accepts an instance of OpenFunctionRegistry and retrieves its internal registry and namespaces.
     *
     * @param OpenFunctionRegistry $registry
     */
    public function __construct(OpenFunctionRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function present(ItemList $itemList): void
    {
        if (empty($this->registry->getNamespaces())) {
            return;
        }
        $itemList->prependItems([$this->getNamespacesDeveloperMessage()]);
    }

    /**
     * Create a developer message listing the namespaces.
     *
     * @return DeveloperMessage
     */
    public function getNamespacesDeveloperMessage(): DeveloperMessage
    {
        if ($this->registry->metaModeEnabled()) {
            $lines = [self::$namespaceIntroMeta];
            $lines[] = json_encode($this->registry->listFunctions()->toArray());

        } else {
            $lines = [self::$namespaceIntro];

            foreach ($this->registry->getNamespaces() as $nsName => $nsInfo) {
                $lines[] = "- {$nsName}: {$nsInfo['description']}";
            }
        }


        return new DeveloperMessage(implode("\n", $lines));
    }
}