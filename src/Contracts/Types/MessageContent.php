<?php

namespace OpenFunctions\Core\Contracts\Types;

interface MessageContent
{
    public function asCompletion(): array;
    public function asResponses(): array;
}