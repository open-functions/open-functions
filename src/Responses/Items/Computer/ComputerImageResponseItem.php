<?php

namespace OpenFunctions\Core\Responses\Items\Computer;

use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Responses\ResponseItem;

/**
 * Class for text-based response items.
 *
 * Example output:
 * [
 *   "type" => "text",
 *   "text" => "Operation successful: 12345"
 * ]
 */
class ComputerImageResponseItem extends ComputerResponseItem
{
    /**
     * The text message.
     *
     * @var string
     */
    private string $image_url;

    /**
     * Constructor.
     *
     * @param string $image_url
     */
    public function __construct(string $image_url) {
        parent::__construct(ResponseItem::TYPE_COMPUTER_SCREENSHOT);

        $this->image_url = $image_url;
    }

    /**
     * Convert the text response item to an associative array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'type' => $this->type,
            'image_url' => $this->image_url,
        ];
    }
}