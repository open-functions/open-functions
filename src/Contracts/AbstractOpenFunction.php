<?php

namespace OpenFunctions\Core\Contracts;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Responses\ResponseItem;
use OpenFunctions\Core\Responses\Items\TextResponseItem;
use OpenFunctions\Core\Responses\OpenFunctionResponse;
use Exception;

abstract class AbstractOpenFunction
{
    use HasMetaData;

    /**
     * Optional interceptor callback.
     * If set and returns non-null, its result is used as the method's result.
     *
     * @var callable|null
     */
    protected $interceptorCallback = null;

    /**
     * Sets the interceptor callback.
     *
     * @param callable $callback
     * @return void
     */
    public function interceptCallUsing(callable $callback): void
    {
        $this->interceptorCallback = $callback;
    }

    /**
     * Calls a method and always returns a Response.
     *
     * This method first checks if an interceptor callback is set.
     * If so, it calls the interceptor with the method name and arguments.
     * If the interceptor returns a non-null value, that result is wrapped and returned.
     * Otherwise, the method is executed normally.
     *
     * @param string $methodName The method to call.
     * @param array $arguments The arguments to pass to the method.
     *
     * @return OpenFunctionResponse
     */
    public function callMethod(string $methodName, array $arguments = []): OpenFunctionResponse
    {
        try {
            // If an interceptor callback is set, use its result if not null.
            if ($this->interceptorCallback !== null) {
                $callbackResult = call_user_func($this->interceptorCallback, $methodName, $arguments);
                if ($callbackResult !== null) {
                    return $this->wrapResult($callbackResult);
                }
            }

            if (!method_exists($this, $methodName)) {
                throw new \BadMethodCallException("Method $methodName does not exist.");
            }

            $result = $this->$methodName(...$arguments);

            return $this->wrapResult($result);
        } catch (\Throwable $e) {
            // Wrap exceptions as error responses.
            $errorItem = new TextResponseItem("An error occurred: " . $e->getMessage());
            return new OpenFunctionResponse(OpenFunctionResponse::STATUS_ERROR, [$errorItem]);
        }
    }

    /**
     * Wraps the given result into an OpenFunctionResponse.
     *
     * @param mixed $result
     * @return OpenFunctionResponse
     */
    protected function wrapResult($result): OpenFunctionResponse
    {
        // If a Response is already returned, use it as is.
        if ($result instanceof OpenFunctionResponse) {
            return $result;
        }

        // If a single ResponseItem is returned, wrap it in an array.
        if ($result instanceof ResponseItem) {
            return new OpenFunctionResponse(OpenFunctionResponse::STATUS_SUCCESS, [$result]);
        }

        // If an array is returned, ensure each element is a ResponseItem.
        if (is_array($result)) {
            $items = [];
            foreach ($result as $item) {
                $items[] = $item instanceof ResponseItem ? $item : new TextResponseItem((string)$item);
            }
            return new OpenFunctionResponse(OpenFunctionResponse::STATUS_SUCCESS, $items);
        }

        // For any other type, wrap it in a TextResponseItem.
        return new OpenFunctionResponse(OpenFunctionResponse::STATUS_SUCCESS, [new TextResponseItem((string)$result)]);
    }

    public function asCompletion(): array
    {
        return array_map(function($item) {
            unset($item['type']);

            return [
                "type" => "function",
                "function" => $item
            ];
        }, $this->generateFunctionDefinitions());
    }

    public function asResponses(): array
    {
        return $this->generateFunctionDefinitions();
    }

    abstract public function generateFunctionDefinitions(): array;
}