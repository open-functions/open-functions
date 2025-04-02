<?php

namespace OpenFunctions\Core\Providers\OpenAI\Maps;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;

class FinishReasonMap
{
    public static function map(string $reason): string
    {
        return match ($reason) {
            'stop', => ProviderResponse::FINISH_REASON_STOP,
            'tool_calls' => ProviderResponse::FINISH_REASON_TOOL_CALLS,
            'length' => ProviderResponse::FINISH_REASON_LENGTH,
            'content_filter' => ProviderResponse::FINISH_REASON_CONTENT_FILTER,
            default => ProviderResponse::FINISH_REASON_UNKNOWN,
        };
    }
}