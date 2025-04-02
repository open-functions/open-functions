<?php

namespace OpenFunctions\Core\Responses;

use OpenFunctions\Core\Contracts\Responses\ResponseItem;
use OpenFunctions\Core\Responses\Items\AudioResponseItem;
use OpenFunctions\Core\Responses\Items\ImageResponseItem;

class OpenFunctionResponse
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR   = 'error';
    public const STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';

    /**
     * @var ResponseItem[] A list of response item objects.
     */
    public array $content = [];

    /**
     * Indicates whether this response represents an error.
     *
     * @var bool
     */
    public bool $isError = false;

    /**
     * The response status.
     *
     * @var string
     */
    public string $status;

    /**
     * Constructor.
     *
     * @param string $status  Should be either self::STATUS_SUCCESS or self::STATUS_ERROR.
     * @param array  $content A list of response items (instances of ResponseItem).
     */
    public function __construct(string $status, array $content = [])
    {
        $this->status  = $status;
        $this->isError = ($status === self::STATUS_ERROR);

        foreach ($content as $item) {
            $this->addContent($item);
        }
    }

    public function addContent(ResponseItem $content): void
    {
        $this->content[] = $content;
    }

    /**
     * @return ImageResponseItem[]
     */
    public function getBinaryItems(): array
    {
        $response = [];

        foreach ($this->content as $item) {
            if ( $item instanceof ImageResponseItem || $item instanceof AudioResponseItem) {
                $response[] = $item;
            }
        }

        return $response;
    }

    public function getOutput($withBinary = true): string
    {
        $response = [];

        foreach ($this->content as $item) {
            if ($withBinary === false && ($item instanceof ImageResponseItem || $item instanceof AudioResponseItem)) {
                continue;
            }

            $response[] = $item->toArray();
        }

        return json_encode($response);
    }
}
