<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Item;

class WebSearchToolCall implements Item
{
    use HasMetaData;

    public string $id;
    public ?string $status;

    public static function make(string $id, string $status): static
    {
        return new static($id, $status);
    }

    public static function id(string $id): static
    {
        return new static($id);
    }

    public function __construct(string $id, ?string $status = null)
    {
        $this->id = $id;
        $this->status = $status;
    }

    public function asCompletion(): array
    {
        throw new \Exception("WebSearchToolCall not supported on Completions API");
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($allowItemReference) {
            return ItemReference::id($this->id)->asResponses();
        }

        return [
            "id" => $this->id,
            "status" => $this->status,
            "type" => "web_search_call"
        ];
    }

    public static function fromResponsesArray(array $data): static
    {
        return new static($data['id'], $data['status'] ?? null);
    }
}