<?php

namespace OpenFunctions\Core\Types\Computer;

class SafetyCheck
{
    public string $id;
    public string $code;
    public string $message;

    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = $data['id'] ?? '';
        $instance->code = $data['code'] ?? '';
        $instance->message = $data['message'] ?? '';
        return $instance;
    }

    public static function listFromArray(array $data): array
    {
        $response = [];

        foreach ($data as $element) {
            $response[] = self::fromArray($element);
        }

        return $response;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}