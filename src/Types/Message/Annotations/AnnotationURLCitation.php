<?php

namespace OpenFunctions\Core\Types\Message\Annotations;

use OpenFunctions\Core\Contracts\Types\Annotation;

class AnnotationURLCitation extends Annotation
{
    public string $type = 'url_citation';
    public int $start_index;
    public int $end_index;
    public string $title;
    public string $url;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->start_index = $data['start_index'] ?? 0;
        $instance->end_index = $data['end_index'] ?? 0;
        $instance->title = $data['title'] ?? '';
        $instance->url = $data['url'] ?? '';
        return $instance;
    }

    public function asCompletion(): array
    {
        return [
            'type' => $this->type,
            'start_index' => $this->start_index,
            'end_index' => $this->end_index,
            'title' => $this->title,
            'url' => $this->url,
        ];
    }

    public function asResponses(): array
    {
        return [
            'type' => $this->type,
            'start_index' => $this->start_index,
            'end_index' => $this->end_index,
            'title' => $this->title,
            'url' => $this->url,
        ];
    }
}