<?php

namespace OpenFunctions\Core\Responses\Items;

use OpenFunctions\Core\Contracts\Responses\ResponseItem;


class ImageResponseItem extends ResponseItem
{
    /**
     * Optional MIME type of the binary resource.
     *
     * @var string|null
     */
    private ?string $mimeType;

    /**
     * Optional base64 encoded binary data.
     *
     * @var string|null
     */
    private ?string $data;

    /**
     * Constructor.
     *
     * @param string $data
     * @param string $mimeType Optional MIME type.
     */
    public function __construct(string $data, string $mimeType) {
        parent::__construct(ResponseItem::TYPE_IMAGE);

        $this->mimeType = $mimeType;
        $this->data = $data;
    }

    /**
     * Convert the binary response item to an associative array.
     *
     * @return array
     */
    public function toArray(): array {
        $result = [
            'type' => $this->type,
            'data' => $this->data,
            'mimeType' => $this->mimeType
        ];

        return $result;
    }
}