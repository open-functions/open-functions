<?php

namespace OpenFunctions\Core\Contracts\Responses;

use OpenFunctions\Core\Responses\Items\Computer\ComputerFileResponseItem;
use OpenFunctions\Core\Responses\Items\Computer\ComputerImageResponseItem;

abstract class ComputerResponseItem extends ResponseItem
{
    public static function fromResponsesArray(array $data = []): ?ComputerResponseItem
    {
        if (empty($data)) {
            return null;
        }

        if (isset($data['file_id'])) {
            return new ComputerFileResponseItem($data['file_id']);
        }

        return new ComputerImageResponseItem($data['image_url']);
    }
}