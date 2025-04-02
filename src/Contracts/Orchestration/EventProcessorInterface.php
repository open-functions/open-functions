<?php

namespace OpenFunctions\Core\Contracts\Orchestration;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Responses\OpenFunctionResponse;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Types\FunctionCall;

interface EventProcessorInterface
{
    // Event name constants
    public const string EVENT_ON_RUN_START                = 'onRunStart';
    public const string EVENT_ON_LOOP_START               = 'onLoopStart';
    public const string EVENT_ON_RUN_STEP_START           = 'onRunStepStart';
    public const string EVENT_ON_BEFORE_PROVIDER_REQUEST  = 'onBeforeProviderRequest';
    public const string EVENT_ON_ITEM_CREATION            = 'onItemCreation';
    public const string EVENT_ON_FUNCTION_CALL            = 'onFunctionCall';
    public const string EVENT_ON_FUNCTION_CALL_FINISHED   = 'onFunctionCallFinished';
    public const string EVENT_ON_COMPUTER_CALL            = 'onComputerCall';
    public const string EVENT_ON_COMPUTER_CALL_FINISHED   = 'onComputerCallFinished';
    public const string EVENT_ON_RUN_STEP_FINISHED        = 'onRunStepFinished';
    public const string EVENT_ON_LOOP_FINISHED            = 'onLoopFinished';
    public const string EVENT_ON_RUN_FINISHED             = 'onRunFinished';
    public const string EVENT_ON_ERROR                    = 'onError';

    /**
     * @return void
     */
    public function onRunStart(): void;

    /**
     * @param LoopInterface $loop
     *
     * @return void
     */
    public function onLoopStart(LoopInterface $loop): void;

    /**
     * Handle an event when a run step is started.
     *
     * @param LoopInterface $loop
     * @param int         $iteration The current iteration number.
     */
    public function onRunStepStart(LoopInterface $loop, int $iteration): void;

    /**
     * Handle an event before a provider request is made
     *
     * @param array $payload
     */
    public function onBeforeProviderRequest(array $payload): void;

    /**
     * Handle a new assistant message event.
     *
     * @param Item $newItem
     * @param LoopInterface $loop
     * @param ProviderResponse $response The original response object.
     */
    public function onItemCreation(Item $newItem, LoopInterface $loop, ProviderResponse $response): void;

    /**
     * @param FunctionCall $functionCall
     * @param LoopInterface $loop
     * @param ProviderResponse $response
     * @return void
     */
    public function onFunctionCall(FunctionCall $functionCall, LoopInterface $loop, ProviderResponse $response): void;

    /**
     * @param FunctionCall $functionCall
     * @param OpenFunctionResponse $functionResponse
     * @param LoopInterface $loop
     * @param ProviderResponse $response
     * @return void
     */
    public function onFunctionCallFinished(FunctionCall $functionCall, OpenFunctionResponse $functionResponse, LoopInterface $loop, ProviderResponse $response): void;

    /**
     * @param ComputerCall $computerCall
     * @param LoopInterface $loop
     * @param ProviderResponse $response
     * @return void
     */
    public function onComputerCall(ComputerCall $computerCall, LoopInterface $loop, ProviderResponse $response): void;

    /**
     * @param ComputerCall $computerCall
     * @param ComputerResponseItem $computerResponseItem
     * @param LoopInterface $loop
     * @param ProviderResponse $response
     * @return void
     */
    public function onComputerCallFinished(ComputerCall $computerCall, ComputerResponseItem $computerResponseItem, LoopInterface $loop, ProviderResponse $response): void;

    /**
     * @param LoopInterface $loop
     * @param ProviderResponse $response
     * @return void
     */
    public function onRunStepFinished(LoopInterface $loop, ProviderResponse $response): void;


    public function onLoopFinished(LoopInterface $loop, string $loopRunFinishReason): void;

    /**
     * Handle an event when the run is finished.
     *
     * @param LoopInterface $loop
     * @param string $runFinishReason
     */
    public function onRunFinished(LoopInterface $loop, string $runFinishReason): void;

    /**
     * Handle an error event.
     *
     * @param \Throwable $exception
     */
    public function onError(\Throwable $exception): void;
}