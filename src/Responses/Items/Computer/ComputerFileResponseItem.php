<?php

namespace OpenFunctions\Core\Responses\Items\Computer;

use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Responses\ResponseItem;

class ComputerFileResponseItem extends ComputerResponseItem
{
    /**
     * The text message.
     *
     * @var string
     */
    private string $fileId;

    /**
     * Constructor.
     *
     * @param string $image_url
     */
    public function __construct(string $fileId) {
        parent::__construct(ResponseItem::TYPE_COMPUTER_SCREENSHOT);

        $this->fileId = $fileId;
    }

    /**
     * Convert the text response item to an associative array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'type' => $this->type,
            'file_id' => $this->fileId,
        ];
    }
}