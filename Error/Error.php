<?php

namespace PHP_Library\Error;

class Error extends \Error
{
    use FormatTrait;

    public bool $initialized = true;

    protected static $errno = E_ERROR;

    public static function if(bool $condition, string $message, int $code = 0)
    {
        if ($condition) {
            throw new self($message, $code);
        }
    }

    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);

        self::initialize();

        if ($trace = $this->getTrace()) {
            $trace = @end($trace);
            $this->file = $trace['file'];
            $this->line = $trace['line'];
        }
    }

    /**
     * Initialize the custom error handler for handling user PHP_Library\Notices and PHP_Library\Warnings.
     * This method sets up a custom error handler for E_USER_WARNING and E_USER_NOTICE.
     */
    private static function initialize(): bool
    {
        set_error_handler(
            function ($errno, $message, $file, $line) {
                if ($errno === self::$errno) {
                    echo self::format_message($message, $errno) . " in {$file}:{$line}\n";
                }
            }
        );

        set_exception_handler(
            function ($exception) {
                if (get_class($exception) === __CLASS__) {
                    echo self::format_message($exception->message, $exception->code) . " in {$exception->file}:{$exception->line}\n";
                }
            }
        );
        return true;
    }
}
