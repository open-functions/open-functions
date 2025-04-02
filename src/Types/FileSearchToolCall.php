<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Types\FileSearch\Result;

class FileSearchToolCall implements Item
{
    use HasMetaData;

    public const string STATUS_SEARCHING = 'searching';

    public string $id;
    public array $queries = [];
    public ?string $status = null;
    /**
     * @var Result[]
     */
    public ?array $results = null;
    public string $type = "file_search_call";

    public static function fromResponsesArray($data): static
    {
        $result = [];
        if (isset($data['results'])) {
            foreach ($data['results'] as $resultData) {
                $result[] = Result::fromArray($resultData);
            }
        }

        return new static($data['id'], $data['queries'] ?? [], $data['status'] ?? null, $result);
    }

    public static function make(string $id, array $queries, string $status, ?array $result = null): static
    {
        return new static($id, $queries, $status, $result);
    }

    public static function id(string $id): static
    {
        return new static($id);
    }

    public function __construct(string $id, array $queries = [], ?string $status = null, ?array $result = null)
    {
        $this->id = $id;
        $this->queries = $queries;
        $this->status = $status;
        $this->results = $result;
    }

    public function asCompletion(): array
    {
        throw new \Exception("FileSearchToolCall not supported on Completions API");
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($allowItemReference) {
            return ItemReference::id($this->id)
                ->asResponses();
        }

        $resultData = [];

        foreach ($this->results as $result) {
            $resultData[] = $result->toArray();
        }

        return [
            "id" => $this->id,
            "queries" => $this->queries,
            "status" => $this->status,
            "type" => $this->type,
            "result" => $resultData
        ];
    }
}