<?php

namespace OpenFunctions\Core\Orchestration\Processors;

use OpenFunctions\Core\Contracts\Orchestration\EventProcessorInterface;
use OpenFunctions\Core\Contracts\Orchestration\LoopInterface;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\Types\FunctionCall;
use OpenFunctions\Core\Responses\OpenFunctionResponse;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;

class InlineEventProcessor implements EventProcessorInterface
{
    /**
     * Holds the registered closures.
     *
     * @var array<string, callable>
     */
    private array $listeners = [];

    /**
     * Registers a closure for the given event.
     *
     * @param string   $eventName One of the EventProcessorInterface constants.
     * @param callable $listener  The listener closure.
     */
    public function addListener(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName] = $listener;
    }

    public function onRunStart(): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_RUN_START])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_RUN_START]);
        }
    }

    public function onLoopStart(LoopInterface $loop): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_LOOP_START])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_LOOP_START], $loop);
        }
    }

    public function onRunStepStart(LoopInterface $loop, int $iteration): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_RUN_STEP_START])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_RUN_STEP_START], $loop, $iteration);
        }
    }

    public function onBeforeProviderRequest(array $payload): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_BEFORE_PROVIDER_REQUEST])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_BEFORE_PROVIDER_REQUEST], $payload);
        }
    }

    public function onItemCreation(Item $newItem, LoopInterface $loop, ProviderResponse $response): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_ITEM_CREATION])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_ITEM_CREATION], $newItem, $loop, $response);
        }
    }

    public function onFunctionCall(FunctionCall $functionCall, LoopInterface $loop, ProviderResponse $response): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_FUNCTION_CALL])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_FUNCTION_CALL], $functionCall, $loop, $response);
        }
    }

    public function onFunctionCallFinished(FunctionCall $functionCall, OpenFunctionResponse $functionResponse, LoopInterface $loop, ProviderResponse $response): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_FUNCTION_CALL_FINISHED])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_FUNCTION_CALL_FINISHED], $functionCall, $functionResponse, $loop, $response);
        }
    }

    public function onComputerCall(ComputerCall $computerCall, LoopInterface $loop, ProviderResponse $response): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_COMPUTER_CALL])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_COMPUTER_CALL], $computerCall, $loop, $response);
        }
    }

    public function onComputerCallFinished(ComputerCall $computerCall, ComputerResponseItem $computerResponseItem, LoopInterface $loop, ProviderResponse $response): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_COMPUTER_CALL_FINISHED])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_COMPUTER_CALL_FINISHED], $computerCall, $computerResponseItem, $loop, $response);
        }
    }

    public function onRunStepFinished(LoopInterface $loop, ProviderResponse $response): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_RUN_STEP_FINISHED])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_RUN_STEP_FINISHED], $loop, $response);
        }
    }

    public function onLoopFinished(LoopInterface $loop, string $loopRunFinishReason): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_LOOP_FINISHED])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_LOOP_FINISHED], $loop, $loopRunFinishReason);
        }
    }

    public function onRunFinished(LoopInterface $loop, string $runFinishReason): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_RUN_FINISHED])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_RUN_FINISHED], $loop, $runFinishReason);
        }
    }

    public function onError(\Throwable $exception): void {
        if (isset($this->listeners[EventProcessorInterface::EVENT_ON_ERROR])) {
            call_user_func($this->listeners[EventProcessorInterface::EVENT_ON_ERROR], $exception);
        }
    }
}