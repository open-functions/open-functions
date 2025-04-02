<?php

namespace OpenFunctions\Core\Orchestration\Models;

class LoopRunResponse
{
    public const string LOOP_FINISH_REASON_STOP = 'stop';
    public const string LOOP_FINISH_REASON_ERROR = 'error';
    public const string LOOP_FINISH_REASON_MAX_ITERATION  = 'max_iterations';

    public string $finishReason;
    public bool $errorOccurred;

    public function __construct(string $finishReason, bool $errorOccurred)
    {
        $this->finishReason = $finishReason;
        $this->errorOccurred = $errorOccurred;
    }
}