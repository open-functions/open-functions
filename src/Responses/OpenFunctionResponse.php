<?php

namespace OpenFunctions\Core\Responses;

use OpenFunctions\Core\Contracts\Responses\ResponseItem;
use OpenFunctions\Core\Responses\Events\AvailableEvent;
use OpenFunctions\Core\Responses\Items\BinaryResponseItem;

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
     * List of available events for subscription.
     *
     * @var AvailableEvent[]
     */
    public array $availableEvents = [];

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
    public function __construct(string $status, array $content = [], array $availableEvents = [])
    {
        $this->status  = $status;
        $this->isError = ($status === self::STATUS_ERROR);

        foreach ($content as $item) {
            $this->addContent($item);
        }

        foreach ($availableEvents as $availableEvent) {
            $this->addAvailableEvent($availableEvent);
        }
    }

    public function addContent(ResponseItem $content): void
    {
        $this->content[] = $content;
    }

    public function addAvailableEvent(AvailableEvent $availableEvent): void
    {
        $this->availableEvents[] = $availableEvent;
    }

    /**
     * @return BinaryResponseItem[]
     */
    public function getBinaryItems(): array
    {
        $response = [];

        foreach ($this->content as $item) {
            if ($item instanceof BinaryResponseItem) {
                $response[] = $item;
            }
        }

        return $response;
    }

    public function getOutput($withBinary = true): string
    {
        $response = [];

        foreach ($this->content as $item) {
            if ($withBinary === false && ($item instanceof BinaryResponseItem)) {
                continue;
            }

            $response[] = $item->toArray();
        }

        return json_encode($response);
    }
}
