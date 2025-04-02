<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Item;

class FunctionCall implements Item
{
    use HasMetaData;

    public ?string $status = null;
    public ?string $callId;
    public ?string $functionName;
    public ?string $functionArguments;
    public ?string $id = null;

    /**
     * @param string $callId
     * @param string $functionName
     * @param string $functionArguments
     * @param string|null $id
     * @param string|null $status
     *
     * @return static
     */
    public static function make(
        string $callId,
        string $functionName,
        string $functionArguments = "{}",
        ?string $id = null,
        ?string $status= null
    ): static
    {
        return new static($callId, $functionName, $functionArguments, $id, $status);
    }


    public static function fromResponsesArray(array $data): static
    {
        return new static(
            $data['call_id'],
            $data['name'],
            $data['arguments'] ?? "{}",
            $data['id'] ?? null,
            $data['status'] ?? null
        );
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function id(string $id): static
    {
        return new static(null, null, null, $id, null);
    }

    public function __construct(
        ?string $callId = null,
        ?string $functionName = null,
        ?string $functionArguments = null,
        ?string $id = null,
        ?string $status= null
    ) {

        $this->status = $status;
        $this->callId = $callId;
        $this->functionName = $functionName;
        $this->functionArguments = $functionArguments;
        $this->id = $id;
    }

    public function asCompletion(): array
    {
        return [
            'role' => 'assistant',
            'tool_calls' => [[
                'id' => $this->callId,
                'type' => 'function',
                'function' => [
                    'name' => $this->functionName,
                    'arguments' => $this->functionArguments,
                ]
            ]]
        ];
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($this->id && $allowItemReference) {
            return ItemReference::id($this->id)
                ->asResponses();
        }

        return [
            'type'      => 'function_call',
            'id'        => $this->id,
            'status'    => $this->status,
            'call_id'   => $this->callId,
            'name'      => $this->functionName,
            'arguments' => $this->functionArguments,
        ];
    }
}