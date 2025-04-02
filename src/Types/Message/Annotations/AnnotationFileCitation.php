<?php

namespace OpenFunctions\Core\Types\Message\Annotations;


use OpenFunctions\Core\Contracts\Types\Annotation;

class AnnotationFileCitation extends Annotation
{
    public string $type = 'file_citation';
    public string $file_id;
    public int $index;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->file_id = $data['file_id'] ?? '';
        $instance->index = $data['index'] ?? 0;
        return $instance;
    }

    public function asCompletion(): array
    {
        return [
            'type' => $this->type,
            'file_id' => $this->file_id,
            'index' => $this->index
        ];
    }

    public function asResponses(): array
    {
        return [
            'type' => $this->type,
            'file_id' => $this->file_id,
            'index' => $this->index
        ];
    }
}
