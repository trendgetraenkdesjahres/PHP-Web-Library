<?php

namespace PHP_Library\Database\Error;

class DatabaseError extends \PHP_Library\Error\Error
{
    public static DatabaseError $last_error;

    public static function trigger(string $message = "", int $code = 0, bool $fatal = false)
    {
        if ($fatal) {
            throw new static($message, $code);
        }
        static::$last_error = new static($message, $code);
    }
}
