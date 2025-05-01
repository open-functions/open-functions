<?php

namespace OpenFunctions\Core\Registry;

use OpenFunctions\Core\Contracts\AbstractComputerTool;
use OpenFunctions\Core\Contracts\AbstractOpenFunction;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Action;
use OpenFunctions\Core\Responses\Items\TextResponseItem;
use OpenFunctions\Core\Responses\OpenFunctionResponse;
use OpenFunctions\Core\Schemas\FunctionDefinition;
use OpenFunctions\Core\Schemas\Parameter;
use Exception;

class OpenFunctionRegistry extends AbstractComputerTool
{
    const NAMESPACE_SEPARATOR = '_';
    const REGISTRY_NAMESPACE_NAME = 'registry';
    public const MAX_ACTIVE_FUNCTIONS = 10;

    protected array $allowedMetaMethods = ['activateFunction', 'deactivateFunction', 'listFunctions'];

    /**
     * Whether the "meta mode" is currently enabled.
     * If false => all function definitions are returned (the old behavior).
     * If true  => only the meta definitions + (optionally) the active namespace's definitions are returned.
     */
    protected bool $metaEnabled = false;

    // Now we track an array of activated functions
    protected array $activeFunctions = [];

    /**
     * Internally stores:
     * [
     *   'namespaceName.functionName' => [
     *       'openFunction' => AbstractOpenFunction,
     *       'method'       => string,  // The actual method on $openFunction
     *       'definition'   => array,   // The function definition array
     *   ],
     * ]
     */
    protected array $registry = [];

    /**
     * Namespaces info:
     * [
     *   'namespaceName' => [
     *       'description' => string,
     *   ],
     *   ...
     * ]
     */
    protected array $namespaces = [];

    protected array $builtInToolDefinitions = [];

    protected ?AbstractComputerTool $computerAsTool = null;

    public function __construct(bool $metaMode = false, ?string $registryNamespaceDescriptions = null)
    {
        $this->metaEnabled = $metaMode;

        if ($this->metaEnabled) {
            if (!$registryNamespaceDescriptions) {
                throw new Exception('please provide a namespace description if you want to activate meta mode');
            }

            $this->registerOpenFunction(self::REGISTRY_NAMESPACE_NAME, $registryNamespaceDescriptions, $this);
        }
    }

    /**
     * Register a single open function under a given namespace.
     *
     * @param string $namespaceName
     * @param string $namespaceDescription
     * @param AbstractOpenFunction $openFunction
     * @throws Exception
     */
    public function registerOpenFunction(
        string $namespaceName,
        string $namespaceDescription,
        AbstractOpenFunction $openFunction
    ): void {
        if ($openFunction instanceof OpenFunctionRegistry) {
            $definitions = $openFunction->getMetaMethodDefinitions();
        } else {
            $definitions = $openFunction->generateFunctionDefinitions();
        }

        if ($openFunction instanceof AbstractComputerTool) {
            if (isset($this->computerAsTool)) {
                throw new Exception('You can only set a computer interface once');
            }

            $this->computerAsTool = $openFunction;
        }

        // Prevent duplicate namespace registration.
        if (isset($this->namespaces[$namespaceName])) {
            throw new Exception('namespace "' . $namespaceName . '" is already registered');
        }

        if (isset($definitions['type'])) {
            $definitions = [$definitions];
        }

        foreach ($definitions as $definition) {
            if ($definition['type'] !== 'function') {
                $this->builtInToolDefinitions[] = $definition;

                continue;
            }

            if (!isset($definition['name'])) {
                throw new Exception("Function definition must contain a 'name' field.");
            }
            $originalName = $definition['name'];
            $namespacedName = $namespaceName . self::NAMESPACE_SEPARATOR . $originalName;

            $definition['name'] = $namespacedName;
            $definition['description'] = "[{$namespaceName}] " . $definition['description'];

            $this->registry[$namespacedName] = [
                'openFunction' => $openFunction,
                'method'       => $originalName,
                'definition'   => $definition,
            ];

            // Store the namespace info only once.
            if (!isset($this->namespaces[$namespaceName])) {
                $this->namespaces[$namespaceName] = [
                    'description' => $namespaceDescription
                ];
            }
        }
    }

    public function resolveOpenFunctionByMethodName(string $methodName): ?AbstractOpenFunction
    {
        // Otherwise, attempt to look up the namespaced function.
        if (!isset($this->registry[$methodName])) {
            return null;
        }

        $entry = $this->registry[$methodName];

        return $entry['openFunction'];
    }

    public function getComputerOpenFunction(): ?AbstractComputerTool
    {
        return $this->computerAsTool;
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @return bool
     */
    public function metaModeEnabled(): bool
    {
        return $this->metaEnabled;
    }

    /**
     * Generate function definitions for:
     *  - The "meta" methods (if meta mode is enabled),
     *  - Potentially the active namespace's definitions (if meta mode is enabled *and* an active namespace is set),
     *  - Or all definitions (if meta mode is disabled).
     */
    public function generateFunctionDefinitions(): array
    {
        // If meta mode is disabled => return ALL definitions from every namespace.
        if (!$this->metaEnabled) {
            return array_merge($this->getAllRegistryDefinitions(), $this->builtInToolDefinitions);
        }

        $activeDefs = [];
        $activeFunctions = array_merge($this->getPrefixedAllowedMetaMethods(), $this->activeFunctions);

        foreach ($activeFunctions as $functionName) {
            if (isset($this->registry[$functionName])) {
                $activeDefs[] = $this->registry[$functionName]['definition'];
            }
        }
        return array_merge($activeDefs, $this->builtInToolDefinitions);
    }

    /**
     * Return all definitions from every namespace in the registry.
     */
    protected function getAllRegistryDefinitions(): array
    {
        $results = [];
        foreach ($this->registry as $entry) {
            $results[] = $entry['definition'];
        }
        return $results;
    }

    /**
     * Build meta method definitions for:
     * - activateFunction: activates multiple functions at once.
     * - deactivateFunction: deactivates multiple functions at once.
     * - listFunctions: returns a list of all functions grouped by namespace.
     */
    protected function getMetaMethodDefinitions(): array
    {
        $metaDefs = [];

        // Activate functions meta method
        $availableFunctions = array_diff(array_keys($this->registry), $this->activeFunctions);
        $activateDef = new FunctionDefinition(
            'activateFunction',
            'Activate functions from the registry. Multiple functions can be activated at once. Duplicates are ignored.'
        );
        $activateParam = Parameter::array('functionNames')
            ->required()
            ->description("An array of valid function names to activate.");
        $activateParam->setItems(Parameter::string('functionName')->description('function name you want to activate'));
        $activateDef->addParameter($activateParam);
        $metaDefs[] = $activateDef->createFunctionDescription();

        // Deactivate functions meta method
        $deactivateDef = new FunctionDefinition(
            'deactivateFunction',
            'Deactivate functions from the registry. Multiple functions can be deactivated at once.'
        );
        $deactivateParam = Parameter::array('functionNames')
            ->required()
            ->description("An array of valid function names to deactivate.");
        $deactivateParam->setItems(Parameter::string('functionName')->description('function name you want to deactivate'));
        $deactivateDef->addParameter($deactivateParam);
        $metaDefs[] = $deactivateDef->createFunctionDescription();

        // List functions meta method
        $listDef = new FunctionDefinition(
            'listFunctions',
            'List all available functions grouped by namespace, including function names, descriptions, and namespace descriptions.'
        );
        $metaDefs[] = $listDef->createFunctionDescription();

        return $metaDefs;
    }

    /**
     * Activate multiple functions by their namespaced names.
     *
     * @param array $functionNames An array of function names to activate.
     * @return array A response message with the activation result.
     */
    public function activateFunction(array $functionNames): TextResponseItem
    {
        $messages = [];
        foreach ($functionNames as $functionName) {
            if (!isset($this->registry[$functionName])) {
                $messages[] = "No such function: '{$functionName}'. Please load all functions and only activate existing names.";
            } elseif (in_array($functionName, $this->activeFunctions, true)) {
                $messages[] = "Function '{$functionName}' is already activated.";
            } else {
                if (count($this->activeFunctions) >= self::MAX_ACTIVE_FUNCTIONS) {
                    $messages[] = "Cannot activate '{$functionName}': maximum limit of " . self::MAX_ACTIVE_FUNCTIONS . " active functions reached. Please deactivate some functions first.";
                    continue;
                }
                $this->activeFunctions[] = $functionName;
                $messages[] = "Function '{$functionName}' activated.";
            }
        }

        return new TextResponseItem(implode("\n", $messages));
    }

    /**
     * Deactivate multiple functions by their namespaced names.
     *
     * @param array $functionNames An array of function names to deactivate.
     * @return TextResponseItem A response message with the deactivation result.
     */
    public function deactivateFunction(array $functionNames): TextResponseItem
    {
        $messages = [];
        foreach ($functionNames as $functionName) {
            if (!in_array($functionName, $this->activeFunctions, true)) {
                $messages[] = "Function '{$functionName}' is not active.";
            } else {
                $index = array_search($functionName, $this->activeFunctions, true);
                if ($index !== false) {
                    array_splice($this->activeFunctions, $index, 1);
                }
                $messages[] = "Function '{$functionName}' deactivated.";
            }
        }

        return new TextResponseItem(implode("\n", $messages));
    }

    /**
     * List all available functions grouped by their namespace.
     *
     * @return TextResponseItem A response message containing the grouped function list.
     */
    public function listFunctions(): TextResponseItem
    {
        $grouped = [];
        foreach ($this->registry as $namespacedName => $entry) {
            $parts = explode(self::NAMESPACE_SEPARATOR, $namespacedName, 2);
            $namespace = $parts[0];
            if (!isset($grouped[$namespace])) {
                $grouped[$namespace] = [
                    'description' => $this->namespaces[$namespace]['description'] ?? '',
                    'functions' => []
                ];
            }
            $grouped[$namespace]['functions'][] = [
                'name' => $namespacedName,
                'description' => $entry['definition']['description']
            ];
        }

        $lines = [];
        foreach ($grouped as $ns => $data) {
            $lines[] = "Namespace '$ns': " . $data['description'];
            foreach ($data['functions'] as $func) {
                $lines[] = "  - {$func['name']}: {$func['description']}";
            }
        }
        return new TextResponseItem(implode("\n", $lines));
    }

    /**
     * Handles meta calls or dispatches them to the underlying open function objects.
     */
    public function callMethod(string $methodName, array $arguments = []): OpenFunctionResponse
    {
        // Otherwise, attempt to look up the namespaced function.
        if (!isset($this->registry[$methodName])) {
            return new OpenFunctionResponse(
                OpenFunctionResponse::STATUS_ERROR,
                [new TextResponseItem("No function registered under '{$methodName}'.")]
            );
        }

        if ($this->metaEnabled) {
            $activeFunctions = array_merge($this->getPrefixedAllowedMetaMethods(), $this->activeFunctions);

            if (in_array($methodName, $activeFunctions, true) === false) {
                return new OpenFunctionResponse(
                    OpenFunctionResponse::STATUS_ERROR,
                    [new TextResponseItem("function is not activated '{$methodName}'. please activate the function first.")]
                );
            }
        }

        $entry = $this->registry[$methodName];
        $openFunction = $entry['openFunction'];
        $actualMethod = $entry['method'];

        if (in_array($methodName, $this->getPrefixedAllowedMetaMethods(), true)) {
            return parent::callMethod($actualMethod, $arguments);
        }

        return $openFunction->callMethod($actualMethod, $arguments);
    }

    public function callComputer(Action $action): ComputerResponseItem
    {
        if (isset($this->computerAsTool) === false) {
            throw new Exception('No computer is activated.');
        }

        return $this->computerAsTool->callComputer($action);
    }

    /**
     * Returns the allowed meta methods with the registry namespace and separator prefixed.
     *
     * @return array
     */
    protected function getPrefixedAllowedMetaMethods(): array {
        return array_map(function(string $method) {
            return self::REGISTRY_NAMESPACE_NAME . self::NAMESPACE_SEPARATOR . $method;
        }, $this->allowedMetaMethods);
    }
}