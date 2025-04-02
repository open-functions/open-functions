<?php

namespace OpenFunctions\Core\Contracts\Types;

interface Item
{
    public const string STATUS_IN_PROGRESS = 'in_progress';
    public const string STATUS_COMPLETED = 'completed';
    public const string STATUS_INCOMPLETE = 'incomplete';

    public function getMetaData(): array;
    public function setMetaData(array $metaData): void;

    public function asCompletion(): array;
    public function asResponses(bool $allowItemReference = true): array;
    public static function fromResponsesArray(array $data): static;
}