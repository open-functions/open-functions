<?php

namespace OpenFunctions\Core\Providers\OpenAI;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;

class OpenAICompletionResponse extends ProviderResponse
{
    public function getProviderIdentifier(): string
    {
        return "open-ai-completion-provider";
    }
}