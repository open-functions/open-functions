<?php

namespace OpenFunctions\Core\Contracts\Types;

use OpenFunctions\Core\Concerns\HasMetaData;
use OpenFunctions\Core\Types\ItemReference;
use OpenFunctions\Core\Types\Message\MessageContentOutputText;
use OpenFunctions\Core\Types\Message\MessageContentText;

abstract class Message implements Item
{
    use HasMetaData;

    public ?string $id = null;
    public string $role;
    public string $type = "message";
    /**
     * @var MessageContent[]
     */
    public array $content = [];
    public ?string $status = null;

    /**
     * @param string|null $message
     * @param string|null $id
     * @param string|null $status
     *
     * @return static
     */
    public static function make(?string $message = null, ?string $id = null, ?string $status= null): static
    {
        return new static($message, $id, $status);
    }

    public static function fromResponsesArray(array $data): static
    {
        $message = new static(null, $data['id'] ?? null, $data['status'] ?? null);
        $text = null;

        foreach ($data['content'] as $content) {
            $text = new MessageContentText($content['text']);
        }

        if ($text) {
            $message->addContent($text);
        }

        return $message;
    }

    /**
     * @param string $id
     *
     * @return static
     */
    public static function id(string $id): static
    {
        return new static(null, $id, null);
    }

    public function addContent(MessageContent $messageContent): static
    {
        $this->content[] = $messageContent;
        return $this;
    }

    public function addContentList(array $contentList): static
    {
        foreach ($contentList as $content) {
            $this->addContent($content);
        }

        return $this;
    }

    public function asCompletion(): array
    {
        $content = [];

        foreach ($this->content as $element) {
            $content[] = $element->asCompletion();
        }

        return [
            'role' => $this->role,
            'content' => $content,
        ];
    }

    public function asResponses(bool $allowItemReference = true): array
    {
        $content = [];

        if ($this->id && $allowItemReference) {
            return ItemReference::id($this->id)
                ->asResponses();
        }

        foreach ($this->content as $element) {
            $content[] = $element->asResponses();
        }

        return [
            'type' => $this->type,
            'role' => $this->role,
            'content' => $content,
            'id' => $this->id,
            'status' => $this->status
        ];
    }

    /**
     * Aggregates all output_text from the message-type output items.
     *
     * @return string The concatenated text from all output_text items.
     */
    public function getText(): string
    {
        $texts = [];

        // Expecting a MessageOutput instance with a 'content' property.
        foreach ($this->content as $content) {
            // Check if the content item is of type 'output_text'
            if ($content instanceof MessageContentOutputText) {
                $texts[] = $content->text;
            } else if ($content instanceof MessageContentText) {
                $texts[] = $content->text;
            }
        }

        return implode("", $texts);
    }
}