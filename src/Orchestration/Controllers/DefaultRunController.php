<?php

namespace OpenFunctions\Core\Orchestration\Controllers;

use OpenFunctions\Core\Contracts\Orchestration\AbstractRunController;
use OpenFunctions\Core\Contracts\Orchestration\LoopInterface;
use OpenFunctions\Core\Contracts\Providers\ProviderResponse;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Responses\OpenFunctionResponse;
use OpenFunctions\Core\Types\ComputerCall;
use OpenFunctions\Core\Types\ComputerCallOutput;
use OpenFunctions\Core\Types\FunctionCall;
use OpenFunctions\Core\Types\FunctionCallOutput;

class DefaultRunController extends AbstractRunController
{
    public function onItemCreation(
        Item $newItem,
        LoopInterface $loop,
        ProviderResponse $response
    ): void {
        // Add the new assistant message to the list.
        $loop->addItem($newItem);
    }

    public function onFunctionCallFinished(
        FunctionCall         $functionCall,
        OpenFunctionResponse $functionResponse,
        LoopInterface        $loop,
        ProviderResponse     $response
    ): void {
        $functionCallOutput = new FunctionCallOutput($functionCall->callId, $functionResponse->getOutput());
        // Add the tool message to the list.
        $loop->addItem($functionCall);
        $loop->addItem($functionCallOutput);
    }

    public function onComputerCallFinished(
        ComputerCall $computerCall,
        ComputerResponseItem $computerResponseItem,
        LoopInterface $loop,
        ProviderResponse $response
    ): void {
        $computerCallOutput = ComputerCallOutput::make($computerCall->callId, $computerResponseItem);
        // Add the tool message to the list.
        $loop->addItem($computerCall);
        $loop->addItem($computerCallOutput);
    }

    public function shouldLoopStop(int $iteration, LoopInterface $loop, ProviderResponse $response): bool
    {
        // If the finish reason is not tool calls
        if ($response->getFinishReason() !== ProviderResponse::FINISH_REASON_TOOL_CALLS) {
            return true;
        }

        return false;
    }
}