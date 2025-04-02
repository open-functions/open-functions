<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Types\Computer\SafetyCheck;

class ComputerCallOutput implements Item
{
    use HasMetaData;

    public ?string $callId = null;

    public ?ComputerResponseItem $output = null;

    /**
     * @var SafetyCheck[]
     */
    public array $acknowledgedSafetyChecks = [];

    public ?string $id = null;

    public ?string $status = null;

    public string $type = "computer_call_output";

    public ?string $currentUrl = null;

    public static function make(
        string $callId,
        ComputerResponseItem $output,
        array $acknowledgedSafetyChecks = [],
        ?string $id = null,
        ?string $status = null
    ): static
    {
        return new static($callId, $output, $acknowledgedSafetyChecks, $id, $status);
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function id(string $id): static
    {
        return new static(null, null, [], $id);
    }

    public static function fromResponsesArray(array $data): static
    {
        return new static(
            $data['call_id'],
            ComputerResponseItem::fromResponsesArray($data['output'] ?? []),
            SafetyCheck::listFromArray($data['acknowledged_safety_checks'] ?? []),
            $data['id'] ?? null,
            $data['status'] ?? null
        );
    }

    public function __construct(
        ?string $callId = null,
        ?ComputerResponseItem $output = null,
        array $acknowledgedSafetyChecks = [],
        ?string $id = null,
        ?string $status = null
    ) {
        $this->callId = $callId;
        $this->output = $output;
        $this->acknowledgedSafetyChecks = $acknowledgedSafetyChecks;
        $this->id = $id;
        $this->status = $status;
    }

    public function asCompletion(): array
    {
        throw new \Exception("ComputerCallOutput not supported on Completions API");
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($this->id && $allowItemReference) {
            return ItemReference::id($this->id)
                ->asResponses();
        }

        $safetyChecks = [];

        foreach ($this->acknowledgedSafetyChecks as $acknowledgedSafetyCheck) {
            $safetyChecks[] = $acknowledgedSafetyCheck->toArray();
        }

        return [
            'type'                          => $this->type,
            'call_id'                       => $this->callId,
            'output'                        => $this->output->toArray(),
            'acknowledged_safety_checks'    => $safetyChecks,
            'id'                            => $this->id,
            'status'                        => $this->status,
            'current_url'                   => $this->currentUrl,
        ];
    }
}