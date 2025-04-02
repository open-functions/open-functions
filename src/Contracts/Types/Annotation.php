<?php

namespace OpenFunctions\Core\Contracts\Types;


use OpenFunctions\Core\Types\Message\Annotations\AnnotationFileCitation;
use OpenFunctions\Core\Types\Message\Annotations\AnnotationFilePath;
use OpenFunctions\Core\Types\Message\Annotations\AnnotationURLCitation;

abstract class Annotation
{
    /**
     * Factory method to return the appropriate annotation instance based on the type.
     *
     * @param array $data
     * @return Annotation
     * @throws \Exception if the annotation type is unknown.
     */
    public static function fromArray(array $data): Annotation
    {
        $type = $data['type'] ?? '';
        return match ($type) {
            'file_citation' => AnnotationFileCitation::fromArray($data),
            'url_citation' => AnnotationURLCitation::fromArray($data),
            'file_path' => AnnotationFilePath::fromArray($data),
            default => throw new \Exception("Unknown annotation type: " . $type),
        };
    }

    public static function annotationListFromArray(array $data): array
    {
        $result = [];

        foreach ($data as $annotation) {
            $result[] = Annotation::fromArray($annotation);
        }

        return $result;
    }

    abstract public function asCompletion(): array;
    abstract public function asResponses(): array;
}