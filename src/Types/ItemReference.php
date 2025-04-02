<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Item;

class ItemReference implements Item
{
    use HasMetaData;

    public string $id;

    public static function id(string $id): static
    {
        return new self($id);
    }

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function asCompletion(): array
    {
        throw new \Exception("Item Reference not supported on Completions API");
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        return [
            "id" => $this->id,
            "type" => "item_reference"
        ];
    }

    public static function fromResponsesArray(array $data): static
    {
        return new self($data['id']);
    }
}