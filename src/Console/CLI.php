<?php

namespace OpenFunctions\Core\Console;

class CLI
{
    public static function input(): string
    {
        // Prompt the user for additional input.
        echo PHP_EOL . "Enter message (or type 'exit' to quit): " ;
        $handle = fopen("php://stdin", "r");
        return trim(fgets($handle));
    }
}