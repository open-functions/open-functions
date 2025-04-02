<?php

namespace OpenFunctions\Core\Orchestration;

use OpenFunctions\Core\Contracts\Orchestration\AbstractRunController;
use OpenFunctions\Core\Contracts\Orchestration\EventProcessorInterface;
use OpenFunctions\Core\Contracts\Orchestration\LoopInterface;
use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\List\ItemList;
use OpenFunctions\Core\Orchestration\Controllers\DefaultRunController;
use OpenFunctions\Core\Orchestration\Models\LoopRunResponse;
use OpenFunctions\Core\Orchestration\Processors\InlineEventProcessor;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Types\FunctionCall;


class AgentLoop
{
    public const string RUN_FINISH_REASON_STOP = 'stop';
    public const string RUN_FINISH_REASON_CANCELED = 'canceled';
    public const string RUN_FINISH_REASON_ERROR = 'error';

    protected AbstractRunController $controller;

    /**
     * Holds all registered processors.
     *
     * @var EventProcessorInterface[]
     */
    protected array $listeners = [];

    /**
     * Inline event processor for registering closures.
     */
    protected InlineEventProcessor $inlineEventProcessor;

    /**
     * Constructor accepts an optional processor.
     * If none is provided, a DefaultProcessor is used.
     *
     * @param AbstractRunController|null $controller
     */
    public function __construct(?AbstractRunController $controller = null)
    {
        if ($controller === null) {
            $controller = new DefaultRunController();
        }

        $this->controller = $controller;
        $this->addEventListener($controller);

        // Initialize and add the inline event processor.
        $this->inlineEventProcessor = new InlineEventProcessor();
        $this->addEventListener($this->inlineEventProcessor);
    }

    /**
     * Registers an additional processor.
     *
     * @param EventProcessorInterface $listener
     */
    public function addEventListener(EventProcessorInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * Helper method to trigger an event on all registered processors.
     *
     * @param string $eventMethod The processor event method name.
     * @param mixed  ...$args     The arguments to pass to the event method.
     */
    protected function triggerEvent(string $eventMethod, ...$args): void
    {
        foreach ($this->listeners as $processor) {
            if (method_exists($processor, $eventMethod)) {
                $processor->$eventMethod(...$args);
            }
        }
    }


    // -- Convenience Methods for Inline Listeners --

    public function onRunStart(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_RUN_START, $listener);
        return $this;
    }

    public function onLoopStart(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_LOOP_START, $listener);
        return $this;
    }

    public function onRunStepStart(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_RUN_STEP_START, $listener);
        return $this;
    }

    public function onBeforeProviderRequest(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_BEFORE_PROVIDER_REQUEST, $listener);
        return $this;
    }

    public function onItemCreation(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_ITEM_CREATION, $listener);
        return $this;
    }

    public function onFunctionCall(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_FUNCTION_CALL, $listener);
        return $this;
    }

    public function onFunctionCallFinished(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_FUNCTION_CALL_FINISHED, $listener);
        return $this;
    }

    public function onComputerCall(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_COMPUTER_CALL, $listener);
        return $this;
    }

    public function onComputerCallFinished(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_COMPUTER_CALL_FINISHED, $listener);
        return $this;
    }

    public function onRunStepFinished(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_RUN_STEP_FINISHED, $listener);
        return $this;
    }

    public function onLoopFinished(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_LOOP_FINISHED, $listener);
        return $this;
    }

    public function onRunFinished(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_RUN_FINISHED, $listener);
        return $this;
    }

    public function onError(callable $listener): self {
        $this->inlineEventProcessor->addListener(EventProcessorInterface::EVENT_ON_ERROR, $listener);
        return $this;
    }

    /**
     * Runs the orchestration loop.
     *
     * @param LoopInterface[] $loops
     *
     * @return ItemList|null The final message list after running.
     */
    public function run(array $loops): ?ItemList
    {
        $lastLoop = null;
        $errorOccurred = false;
        $runFinishReason = self::RUN_FINISH_REASON_STOP;

        $this->triggerEvent(EventProcessorInterface::EVENT_ON_RUN_START);

        foreach ($loops as $loop) {
            $lastLoop = $loop;

            $this->triggerEvent(EventProcessorInterface::EVENT_ON_LOOP_START, $loop);

            $loopRunResponse = $this->runLoop($loop);

            if ($loopRunResponse->errorOccurred) {
                $errorOccurred = true;
                $runFinishReason = self::RUN_FINISH_REASON_ERROR;
                break;
            } else {
                $this->triggerEvent(EventProcessorInterface::EVENT_ON_LOOP_FINISHED, $loop, $loopRunResponse->finishReason);
            }

            if ($this->controller->shouldCancelRun($loop)) {
                $runFinishReason = self::RUN_FINISH_REASON_CANCELED;
                break;
            }
        }

        if (!$errorOccurred) {
            // Trigger run finish event.
            $this->triggerEvent(EventProcessorInterface::EVENT_ON_RUN_FINISHED, $lastLoop, $runFinishReason);
        }

        return $lastLoop->getItemList();
    }

    /**
     * Runs a single loop, triggering events on all processors.
     *
     * @param LoopInterface $loop
     * @return LoopRunResponse
     */
    protected function runLoop(LoopInterface $loop): LoopRunResponse
    {
        $loop->start();

        $errorOccurred = false;
        $iteration = 0;
        $finishReason = LoopRunResponse::LOOP_FINISH_REASON_MAX_ITERATION;

        while ($iteration < $loop->getMaxIterations()) {
            // Trigger run step start event.
            $this->triggerEvent(EventProcessorInterface::EVENT_ON_RUN_STEP_START, $loop, $iteration);

            // Before calling the provider request, check for a before-request callback.
            $loop->getProvider()->onBeforeRequest(function (array $payload) {
                $this->triggerEvent(EventProcessorInterface::EVENT_ON_BEFORE_PROVIDER_REQUEST, $payload);
            });

            try {
                $response = $loop->getProvider()
                    ->request($loop->getParams(), $loop->getItemList(), $loop->getOpenFunction(), $loop->getPresenters(), $loop->getFilters());

                foreach ($response->getOutputItems()->getItems() as $item) {
                    if ($item instanceof FunctionCall) {
                        $this->processFunctionCall($item, $loop, $response);
                    } elseif ($item instanceof ComputerCall) {
                        $this->processComputerCall($item, $loop, $response);
                    } else {
                        // Trigger item creation event.
                        $this->triggerEvent(EventProcessorInterface::EVENT_ON_ITEM_CREATION, $item, $loop, $response);
                    }
                }
            } catch (\Throwable $e) {
                // Trigger error event.
                $this->triggerEvent(EventProcessorInterface::EVENT_ON_ERROR, $e);
                $errorOccurred = true;
                $finishReason = LoopRunResponse::LOOP_FINISH_REASON_ERROR;
                break;
            }

            // Trigger run step finish event.
            $this->triggerEvent(EventProcessorInterface::EVENT_ON_RUN_STEP_FINISHED, $loop, $response);

            if ($this->controller->shouldLoopStop($iteration, $loop, $response) === true) {
                $finishReason = LoopRunResponse::LOOP_FINISH_REASON_STOP;
                break;
            }

            $iteration++;
        }

        $loop->stop();

        return new LoopRunResponse($finishReason, $errorOccurred);
    }

    /**
     * Processes a function call by triggering related events on all processors.
     *
     * @param FunctionCall     $functionCall
     * @param LoopInterface             $loop
     * @param ProviderResponse $response
     */
    protected function processFunctionCall(FunctionCall $functionCall, LoopInterface $loop, ProviderResponse $response): void
    {
        $this->triggerEvent(EventProcessorInterface::EVENT_ON_FUNCTION_CALL, $functionCall, $loop, $response);

        $toolResponse = $loop->getOpenFunction()
            ->callMethod($functionCall->functionName, json_decode($functionCall->functionArguments, true));

        $this->triggerEvent(EventProcessorInterface::EVENT_ON_FUNCTION_CALL_FINISHED, $functionCall, $toolResponse, $loop, $response);
    }

    /**
     * Processes a computer call by triggering related events on all processors.
     *
     * @param ComputerCall     $computerCall
     * @param LoopInterface             $loop
     * @param ProviderResponse $response
     */
    protected function processComputerCall(ComputerCall $computerCall, LoopInterface $loop, ProviderResponse $response): void
    {
        $this->triggerEvent(EventProcessorInterface::EVENT_ON_COMPUTER_CALL, $computerCall, $loop, $response);

        $computerResponse = $loop->getOpenFunction()
            ->callComputer($computerCall->action);

        $this->triggerEvent(EventProcessorInterface::EVENT_ON_COMPUTER_CALL_FINISHED, $computerCall, $computerResponse, $loop, $response);
    }
}