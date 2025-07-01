<?php

namespace PHP_Library\Error;

use PHP_Library\Debug\Context;

class Notice
{
    use MessageFormatTrait;
    
    /**
     * Notice class extends the base \Exception class and handles custom PHP_Library\Notices.
     */
    private static $initialized = false;

    protected static $user_errno = E_USER_NOTICE;
    protected static $errno = E_NOTICE;

    /**
     * Constructor for the Notice class.
     *
     * @param string $message The message to be associated with this notice.
     */
    public function __construct(public string $message)
    {
        if (!self::$initialized) {
            self::$initialized = $this::initialize();
        }
        trigger_error(
            message: $this->message,
            error_level: self::$user_errno
        );
    }

    public static function if(bool $condition, string $message)
    {
        if ($condition) {
            new self($message);
        }
    }

    /**
     * Trigger a custom notice with a message and additional information.
     *
     * @param string $message The notice message to display.
     */
    public static function trigger(string $message): void
    {
        new self($message);
    }

    /**
     * Initialize the custom error handler for handling user PHP_Library\Notices and PHP_Library\Warnings.
     * This method sets up a custom error handler for E_USER_WARNING and E_USER_NOTICE.
     */
    private static function initialize(): bool
    {
        set_error_handler(
            function ($errno, $message, $file, $line) {
                if ($errno === self::$errno || $errno === self::$user_errno) {
                    $context = new Context();
                    echo static::format_message($message, null, $context->get_method(),  $context->get_file());
                    return true;
                }
                return false;
            }
        );
        set_exception_handler(
            function ($exception) {
                if (get_class($exception) === __CLASS__) {
                    $context = new Context();
                    echo static::format_message($exception->message, null, $context->method, $context->get_file(), null);
                    return true;
                }
                return false;
            }
        );
        return true;
    }
}
