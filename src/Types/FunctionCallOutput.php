<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Item;

class FunctionCallOutput implements Item
{
    use HasMetaData;

    public ?string $callId = null;
    public ?string $output = null;
    public ?string $id = null;
    public ?string $status = null;

    /**
     * @param string $callId
     * @param string $output
     * @param string|null $id
     * @param string|null $status
     *
     * @return static
     */
    public static function make(string $callId, string $output, ?string $id = null, ?string $status= null): static
    {
        return new static($callId, $output, $id, $status);
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function id(string $id): static
    {
        return new static(null, $id, null, null);
    }

    public static function fromResponsesArray(array $data): static
    {
        return new static($data['call_id'], $data['output'], $data['id'] ?? null, $data['status'] ?? null);
    }

    /**
     * @param string|null $callId
     * @param string|null $output
     * @param string|null $id
     *
     * @param string|null $status
     */
    public function __construct(?string $callId = null, ?string $output = null, ?string $id = null, ?string $status= null)
    {
        $this->callId = $callId;
        $this->output = $output;
        $this->id = $id;
        $this->status = $status;
    }

    public function asCompletion(): array
    {
        return [
            'role' => 'tool',
            'tool_call_id' => $this->callId,
            'content' => $this->output,
        ];
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($this->id && $allowItemReference) {
            return ItemReference::id($this->id)
                ->asResponses();
        }

        return [
            'type'      => 'function_call_output',
            'call_id'   => $this->callId,
            'output'    => $this->output,
            'id'        => $this->id,
            'status'    => $this->status,
        ];
    }
}