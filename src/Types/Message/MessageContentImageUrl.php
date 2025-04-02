<?php

namespace OpenFunctions\Core\Types\Message;

use OpenFunctions\Core\Contracts\Types\MessageContent;

class MessageContentImageUrl implements MessageContent
{
    public string $url;
    public string $detail;

    public function __construct(string $url, string $detail = 'auto')
    {
        $this->url = $url;
        $this->detail = $detail;
    }

    public function asCompletion(): array
    {
        return [
            'type' => 'image_url',
            'image_url' => [
                "url" => $this->url,
                "detail" => $this->detail,
            ]
        ];
    }

    public function asResponses(): array
    {
        return [
            'detail' => $this->detail,
            'type' => 'input_image',
            'image_url' => $this->url
        ];
    }

    public function asBase64(): string
    {
        $content = file_get_contents($this->url);

        if ($content === '' || $content === '0' || $content === false) {
            throw new \Exception("{$this->url} is empty");
        }

        return base64_encode($content);
    }
}