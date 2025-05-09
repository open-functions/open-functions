<?php

namespace OpenFunctions\Core\Responses\Items;

use OpenFunctions\Core\Contracts\Responses\ResponseItem;


class BinaryResponseItem extends ResponseItem
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
    private ?string $uri;

    /**
     * Constructor.
     *
     * @param string $data
     * @param string $mimeType Optional MIME type.
     */
    public function __construct(string $uri, string $mimeType, string $data) {
        parent::__construct(ResponseItem::TYPE_BINARY);

        $this->mimeType = $mimeType;
        $this->data = $data;
        $this->uri = $uri;
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
            'uri' => $this->uri,
            'mimeType' => $this->mimeType
        ];

        return $result;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }
}