<?php

namespace OpenFunctions\Core\Contracts\Responses;

/**
 * Abstract base class for response items.
 */
abstract class ResponseItem
{
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_AUDIO = 'audio';
    const TYPE_COMPUTER_SCREENSHOT = 'computer_screenshot';

    /**
     * The type of the response item.
     *
     * @var string
     */
    protected string $type;

    /**
     * Constructor.
     *
     * @param string $type The type of the response item.
     */
    public function __construct(string $type) {
        $this->type = $type;
    }

    /**
     * Convert the response item to an associative array.
     *
     * @return array
     */
    abstract public function toArray(): array;
}