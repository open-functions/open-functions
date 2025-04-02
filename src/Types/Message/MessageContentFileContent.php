<?php

namespace OpenFunctions\Core\Types\Message;

use OpenFunctions\Core\Contracts\Types\MessageContent;

class MessageContentFileContent implements MessageContent
{
    public string $fileContent;
    public ?string $fileName;

    public function __construct(string $fileContent, ?string $fileName = null)
    {
        $this->fileContent = $fileContent;
        $this->fileName = $fileName;
    }

    public function asCompletion(): array
    {
        return [
            'type' => 'file',
            'file' => [
                "file_data" => $this->fileContent,
                "file_name" => $this->fileName,
            ]
        ];
    }

    public function asResponses(): array
    {
        return [
            'type' => 'input_file',
            "file_data" => $this->fileContent,
            "filename" => $this->fileName,
        ];
    }
}