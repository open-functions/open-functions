<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Contracts\Types\Action;
use OpenFunctions\Core\Contracts\Types\Item;
use OpenFunctions\Core\Types\Computer\SafetyCheck;

class ComputerCall implements Item
{
    use HasMetaData;

    public string $id;
    public ?string $status = null;
    public ?string $callId = null;
    public ?Action $action = null;
    /**
     * @var SafetyCheck[]
     */
    public array $pendingSafetyChecks = [];

    public string $type = "computer_call";

    public function __construct(
        string $id,
        ?string $status = null,
        ?string $callId = null,
        ?Action $action = null,
        $pendingSafetyChecks = []
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->callId = $callId;
        $this->action = $action;
        $this->pendingSafetyChecks = $pendingSafetyChecks;
    }

    /**
     * @param string $id
     * @param string $status
     * @param string $callId
     * @param Action $action
     * @param array $pendingSafetyChecks
     *
     * @return static
     */
    public static function make(string $id, string $status, string $callId, Action $action, array $pendingSafetyChecks = []): static
    {
        return new static($id, $status, $callId, $action, $pendingSafetyChecks);
    }

    public static function fromResponsesArray(array $data): static
    {
        $action = Action::fromArray($data['action']);
        $safetyChecks = SafetyCheck::listFromArray($data['pending_safety_checks'] ?? []);

        return new static($data['id'], $data['status'], $data['call_id'], $action, $safetyChecks);
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


    public function asCompletion(): array
    {
        throw new \Exception("ComputerCall not supported on Completions API");
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        if ($allowItemReference) {
            return ItemReference::id($this->id)
                ->asResponses();
        }

        $pendingSafetyChecks = [];

        foreach ($this->pendingSafetyChecks as $safetyCheck) {
            $pendingSafetyChecks[] = $safetyCheck->toArray();
        }

        return [
            "id" => $this->id,
            "status" => $this->status,
            "call_id" => $this->callId,
            "type" => $this->type,
            "action" => $this->action->toArray(),
            "pending_safety_checks" => $pendingSafetyChecks
        ];
    }
}