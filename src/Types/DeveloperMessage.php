<?php

namespace OpenFunctions\Core\Types;

use OpenFunctions\Core\Contracts\Types\Message;
use OpenFunctions\Core\Types\Message\MessageContentText;

class DeveloperMessage extends Message
{
    public function __construct(?string $message = null, ?string $id = null, ?string $status= null)
    {
        $this->role = 'developer';
        $this->status = $status;
        $this->id = $id;

        if ($message) {
            $this->content[] = new MessageContentText($message);
        }
    }
}