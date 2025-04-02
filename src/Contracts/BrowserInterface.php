<?php

namespace OpenFunctions\Core\Contracts;

interface BrowserInterface
{
    public function getCurrentUrl(): ?string;
    public function getCurrentTarget(): ?string;
}