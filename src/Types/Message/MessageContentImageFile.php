<?php

namespace OpenFunctions\Core\Types\Message;

use OpenFunctions\Core\Contracts\Types\MessageContent;

class MessageContentImageFile implements MessageContent
{
    public string $fileId;
    public string $detail;

    public function __construct(string $fileId, string $detail = 'auto')
    {
        $this->fileId = $fileId;
        $this->detail = $detail;
    }

    /**
     * @throws \Exception
     */
    public function asCompletion(): array
    {
        throw new \Exception('Message with Image File not supported with completion');
    }

    public function asResponses(): array
    {
        return [
            'detail' => $this->detail,
            'type' => 'input_image',
            'file_id' => $this->fileId
        ];
    }
}