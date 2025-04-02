<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Types\Reasoning\Summary;

class Reasoning implements Item
{
    use HasMetaData;

    public string $id;
    public ?string $status = null;
    /**
     * @var Summary[]
     */
    public array $summary = [];

    /**
     * @param $data
     * @return static
     */
    public static function fromResponsesArray($data): static
    {
        $summary = [];

        foreach ($data['summary'] as $summaryData) {
            $summary[] = Summary::fromArray($summaryData);
        }

        return new static($data['id'], $summary, $data['status'] ?? null);
    }

    /**
     * @param string $id
     * @param array $summary
     * @param string|null $status
     *
     * @return static
     */
    public static function make(string $id, array $summary = [], ?string $status = null): static
    {
        return new static($id, $summary, $status);
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function id(string $id): static
    {
        return new static($id);
    }

    public function __construct(string $id, array $summary = [], ?string $status = null)
    {
        $this->id = $id;
        $this->summary = $summary;
        $this->status = $status;
    }

    public function asCompletion(): array
    {
        throw new \Exception("Reasoning not supported on Completions API");
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($allowItemReference) {
            return ItemReference::id($this->id)->asResponses();
        }

        $summary = [];

        foreach ($this->summary as $summaryData) {
            $summary[] = $summaryData->toArray();
        }

        return [
            "id" => $this->id,
            "status" => $this->status,
            "type" => "reasoning",
            'summary' => $summary,
        ];
    }

    public function getReasoningText(): string
    {
        $texts = [];

        // Expecting a MessageOutput instance with a 'content' property.
        foreach ($this->summary as $summary) {
            $texts[] = $summary->text;
        }

        return implode("", $texts);
    }
}