<?php

namespace OpenFunctions\Core\Contracts\Orchestration;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Responses\OpenFunctionResponse;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Types\FunctionCall;

class AbstractEventProcessor implements EventProcessorInterface
{
    public function onRunStart(): void {}

    public function onLoopStart(LoopInterface $loop): void {}

    public function onRunStepStart(LoopInterface $loop, int $iteration): void {}

    public function onBeforeProviderRequest(array $payload): void {}

    public function onItemCreation(
        Item $newItem,
        LoopInterface $loop,
        ProviderResponse $response
    ): void {}

    public function onFunctionCall(
        FunctionCall $functionCall,
        LoopInterface $loop,
        ProviderResponse $response
    ): void {}

    public function onFunctionCallFinished(
        FunctionCall         $functionCall,
        OpenFunctionResponse $functionResponse,
        LoopInterface        $loop,
        ProviderResponse     $response
    ): void {}

    public function onComputerCall(
        ComputerCall $computerCall,
        LoopInterface $loop,
        ProviderResponse $response
    ): void {}

    public function onComputerCallFinished(
        ComputerCall $computerCall,
        ComputerResponseItem $computerResponseItem,
        LoopInterface $loop,
        ProviderResponse $response
    ): void {}

    public function onRunStepFinished(LoopInterface $loop, ProviderResponse $response): void {}

    public function onLoopFinished(LoopInterface $loop, string $loopRunFinishReason): void {}

    public function onRunFinished(LoopInterface $loop, string $runFinishReason): void {}

    public function onError(\Throwable $exception): void {}
}