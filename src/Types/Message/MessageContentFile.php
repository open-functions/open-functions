<?php

namespace OpenFunctions\Core\Types\Message;

use OpenFunctions\Core\Contracts\Types\MessageContent;

class MessageContentFile implements MessageContent
{
    public string $fileId;
    public ?string $fileName;

    public function __construct(string $fileId, ?string $fileName = null)
    {
        $this->fileId = $fileId;
        $this->fileName = $fileName;
    }

    public function asCompletion(): array
    {
        return [
            'type' => 'file',
            'file' => [
                "file_id" => $this->fileId,
                "file_name" => $this->fileName,
            ]
        ];
    }

    public function asResponses(): array
    {
        return [
            'type' => 'input_file',
            "file_id" => $this->fileId,
            "filename" => $this->fileName,
        ];
    }
}