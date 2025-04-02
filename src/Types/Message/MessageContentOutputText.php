<?php

namespace OpenFunctions\Core\Types\Message;

use OpenFunctions\Core\Contracts\Types\Annotation;
use OpenFunctions\Core\Contracts\Types\MessageContent;

class MessageContentOutputText implements MessageContent
{
    public string $text;
    /**
     * @var Annotation[]
     */
    public array $annotations = [];

    public function __construct(string $text, array $annotations = [])
    {
        $this->text = $text;
        $this->annotations = $annotations;
    }

    public function addAnnotations(array $annotations = []): void
    {
        foreach ($annotations as $annotation) {
            $this->addAnnotation($annotation);
        }
    }

    public function addAnnotation(Annotation $annotation): void
    {
        $this->annotations[] = $annotation;
    }

    public function asCompletion(): array
    {
        return [
            'text' => $this->text,
            'type' => 'text',
        ];
    }

    public function asResponses(): array
    {
        if ($this->annotations) {
            $annotationsData = [];

            foreach ($this->annotations as $annotation) {
                $annotationsData[] = $annotation->asResponses();
            }

            return [
                'text' => $this->text,
                'type' => 'output_text',
                'annotations' => $annotationsData,
            ];
        }
        return [
            'text' => $this->text,
            'type' => 'output_text',
        ];
    }
}