<?php

namespace OpenFunctions\Core\Contracts\Providers;

use OpenFunctions\Core\List\ItemList;

/**
 * Interface ProviderResponseInterface
 *
 * This interface defines how a provider response should expose its data.
 */
abstract Class ProviderResponse
{
    protected array $rawInputItems;
    protected ItemList $outputItems;
    protected array $rawFunctionDefinitions;
    protected string $finishReason;
    protected array $rawResponse;

    public const string FINISH_REASON_STOP = 'stop';
    public const string FINISH_REASON_LENGTH = 'length';
    public const string FINISH_REASON_CONTENT_FILTER = 'content_filter';
    public const string FINISH_REASON_TOOL_CALLS = 'tool_calls';
    public const string FINISH_REASON_OTHER = 'other';
    public const string FINISH_REASON_UNKNOWN = 'unknown';

    /**
     * Constructor.
     *
     * @param ItemList $outputItems  The list of processed response items.
     * @param string   $finishReason The reason for finishing the response.
     * @param array    $rawResponse  The raw response array from the OpenAI API.
     */
    public function __construct(array $rawInputItems, array $rawFunctionDefinitions, ItemList $outputItems, string $finishReason, array $rawResponse)
    {
        $this->rawInputItems = $rawInputItems;
        $this->rawFunctionDefinitions = $rawFunctionDefinitions;
        $this->outputItems = $outputItems;
        $this->finishReason = $finishReason;
        $this->rawResponse = $rawResponse;
    }

    abstract public function getProviderIdentifier(): string;

    /**
     * Returns the processed ItemList.
     *
     * @return ItemList
     */
    public function getOutputItems(): ItemList
    {
        return $this->outputItems;
    }

    /**
     * Returns the finish reason.
     *
     * @return string
     */
    public function getFinishReason(): string
    {
        return $this->finishReason;
    }

    public function getRawInputItems(): array
    {
        return $this->rawInputItems;
    }

    public function getRawFunctionDefinitions(): array
    {
        return $this->rawFunctionDefinitions;
    }

    /**
     * Returns the raw API response.
     *
     * @return array
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }
}