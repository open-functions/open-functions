<?php

namespace OpenFunctions\Core\Providers\OpenAI;

use OpenFunctions\Core\Contracts\Providers\ProviderResponse;

class OpenAIResponsesResponse extends ProviderResponse
{
    public function getProviderIdentifier(): string
    {
        return "open-ai-responses-provider";
    }
}