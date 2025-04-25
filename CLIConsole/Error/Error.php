<?php

namespace PHP_Library\CLIConsole\Error;


class Error extends \PHP_Library\Error\Error
{
    protected static function exception_handler($exception): bool
    {
        // Ensure we're handling only instances of this Error class
        if ($exception instanceof static)
        {
            print static::get_emoji($exception) . " " . $exception->getMessage() . "\n";
            return true;
        }
        else
        {
            return false;
        }
    }
}
