<?php

namespace OpenFunctions\Core\Concerns;

trait HasMetaData
{
    public array $metaData = [];

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }
}