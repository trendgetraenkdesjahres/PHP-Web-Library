<?php

namespace PHP_Library\Error;

class Error extends \Error
{
    use FormatTrait;

    private static bool $initialized = false;

    protected static int $exit_code = 1;

    protected string $file;
    protected int $line;

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
            if (isset($trace['file'])) {
                $this->file = $trace['file'];
                $this->line = $trace['line'];
            }
        }
    }

    /**
     * Initialize the custom error handler for handling user PHP_Library\Notices and PHP_Library\Warnings.
     * This method sets up a custom error handler for E_USER_WARNING and E_USER_NOTICE.
     */
    private static function initialize(): bool
    {
        if (static::$initialized) {
            return true;
        }
        set_error_handler(
            function ($errno, $message, $file, $line) {
                // Only handle errors matching self::$errno
                if ($errno === self::$errno) {
                    echo self::format_message($message, $errno) . " in {$file}:{$line}\n";
                } else {
                    // Allow other handlers to process this error
                    return false;
                }
            }
        );
        set_exception_handler(
            function ($exception) {
                // Ensure we're handling only instances of this Error class
                if ($exception instanceof static) {
                    print self::format_message($exception->getMessage(), $exception->getCode()) .
                        " in {$exception->getFile()}:{$exception->getLine()}\n";
                } else {
                    return false;
                }
            }
        );
        static::$initialized = true;
        return true;
    }
}
