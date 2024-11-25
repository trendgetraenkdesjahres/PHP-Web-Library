<?php

namespace PHP_Library\Database\Error;

/**
 * Defines the DatabaseError class that extends from the base Error class.
 * Provides functionality to trigger and store database-related errors.
 * Depends on the `Error` class from the `PHP_Library\Error` namespace.
 */
class DatabaseError extends \PHP_Library\Error\Error
{
    /**
     * Stores the last triggered database error.
     * @var DatabaseError
     */
    public static DatabaseError $last_error;

    /**
     * Triggers a database error, either throwing an exception or saving the error.
     * @param string $message The error message.
     * @param int $code The error code.
     * @param bool $fatal If true, throws an exception, otherwise saves the error in `last_error`.
     */
    public static function trigger(string $message = "", int $code = 0, bool $fatal = false)
    {
        if ($fatal) {
            throw new static($message, $code);
        }
        static::$last_error = new static($message, $code);
    }
}
