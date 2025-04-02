<?php

namespace OpenFunctions\Core\Examples;

use OpenFunctions\Core\Contracts\AbstractComputerTool;
use OpenFunctions\Core\Contracts\BrowserInterface;
use OpenFunctions\Core\Contracts\Responses\ComputerResponseItem;
use OpenFunctions\Core\Contracts\Types\Action;
use OpenFunctions\Core\Responses\Items\Computer\ComputerImageResponseItem;

class ComputerDemoOpenFunction extends AbstractComputerTool implements BrowserInterface
{
    private int $stepNumber;

    private array $urlMap = [
        "https://bing.com",
        "https://bing.com",
        "https://bing.com",
        "https://bing.com",
        "https://bing.com",
        "https://news.ycombinator.com/",
        "https://news.ycombinator.com/",
    ];

    public function __construct($stepNumber = 0)
    {
        $this->stepNumber = $stepNumber;
    }

    public function callComputer(Action $action): ComputerResponseItem
    {
        $this->stepNumber++;

        if ($this->stepNumber > 7) {
            $this->stepNumber = 7;
        }

        // Construct the file path. Assuming screenshots are named 1.png to 7.png.
        $filePath = __DIR__ . "/../../media/computer-demo/{$this->stepNumber}.png";

        // Load the screenshot file contents.
        $screenshotData = @file_get_contents($filePath);
        if ($screenshotData === false) {
            throw new \Exception("Failed to load screenshot from path: {$filePath}");
        }
        
        // Return the image response encoded as a base64 data URL.
        return new ComputerImageResponseItem("data:image/png;base64," . base64_encode($screenshotData));
    }


    public function generateFunctionDefinitions(): array
    {
        $definitions = [];

        $definitions[] = [
            "type" => "computer_use_preview",
            "display_width" => 1024,
            "display_height" => 768,
            "environment" => "browser"
        ];

        return $definitions;
    }

    public function getCurrentUrl(): string
    {
        return $this->urlMap[$this->stepNumber - 1];
    }

    public function getCurrentTarget(): ?string
    {
        return null;
    }
}