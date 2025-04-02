<?php

namespace OpenFunctions\Core\Types\Reasoning;

class Summary
{
    /** @var string The short summary text. */
    public string $text;

    /** @var string The type of the object. Always "summary_text". */
    public string $type = 'summary_text';

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->text = $data['text'] ?? '';
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'type' => $this->type
        ];
    }
}