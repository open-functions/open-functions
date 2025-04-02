<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Contracts\Types\Annotation;
use OpenFunctions\Core\Contracts\Types\Message;
use OpenFunctions\Core\Types\Message\MessageContentOutputText;

class AssistantMessage extends Message
{
    public function __construct(?string $message = null, ?string $id = null, ?string $status= null)
    {
        $this->role = 'assistant';
        $this->status = $status;
        $this->id = $id;

        if ($message) {
            $this->content[] = new MessageContentOutputText($message);
        }
    }

    public static function fromResponsesArray(array $data): static
    {
        $assistantMessage = new AssistantMessage(null, $data['id'] ?? null, $data['status'] ?? null);
        $text = null;

        foreach ($data['content'] as $content) {
            $text = new MessageContentOutputText($content['text']);

            if (isset($content['annotations'])) {
                $annotations = Annotation::annotationListFromArray($content['annotations']);
                $text->addAnnotations($annotations);
            }
        }

        if ($text) {
            $assistantMessage->addContent($text);
        }

        return $assistantMessage;
    }

    /**
     * @param string $message
     * @param string|null $id
     * @param string|null $status
     *
     * @return static
     */
    public static function withAnnotations(string $message, array $annotations = [], ?string $id = null, ?string $status= null): AssistantMessage
    {
        $assistantMessage = new AssistantMessage(null, $id, $status);

        $text = new MessageContentOutputText($message);

        foreach ($annotations as $annotation) {
            $text->addAnnotation($annotation);
        }

        $assistantMessage->addContent($text);

        return $assistantMessage;
    }

    /**
     * Aggregates all annotations from the output items.
     *
     * @return array The combined annotations from all output_text items.
     */
    public function getAnnotations(): array
    {
        $annotations = [];

        // Expecting a MessageOutput instance with a 'content' property.
        foreach ($this->content as $content) {
            // Check if the content item is of type 'output_text'
            if ($content instanceof MessageContentOutputText && $content->annotations) {
                $annotations[] = $content->annotations;
            }
        }

        return $annotations;
    }
}