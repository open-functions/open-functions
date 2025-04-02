<?php

namespace OpenFunctions\Core\Providers\Ollama;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;

class OllamaCompletionResponse extends ProviderResponse
{
    public function getProviderIdentifier(): string
    {
        return "ollama-completion-provider";
    }
}