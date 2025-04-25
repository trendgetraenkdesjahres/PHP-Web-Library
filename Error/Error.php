<?php

namespace PHP_Library\Error;

use ReflectionClass;

class Error extends \Error
{
    use FormatTrait;

    public string $emoji = '😱';

    private static bool $initialized = false;

    protected static int $exit_code = 1;

    protected string $file;
    protected int $line;

    protected static $errno = E_ERROR;

    public static function if(bool $condition, string $message, int $code = 0)
    {
        if ($condition)
        {
            throw new self($message, $code);
        }
    }

    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
        self::initialize();
        if ($trace = $this->getTrace())
        {
            $trace = @end($trace);
            if (isset($trace['file']))
            {
                $this->file = $trace['file'];
                $this->line = $trace['line'];
            }
        }
    }

    /**
     * Initialize the custom error handler for handling user PHP_Library\Notices and PHP_Library\Warnings.
     * This method sets up a custom error handler for E_USER_WARNING and E_USER_NOTICE.
     */
    final protected static function initialize(): bool
    {
        if (static::$initialized)
        {
            return true;
        }
        set_error_handler(function ($errno, $message, $file, $line)
        {
            return static::error_handler($errno, $message, $file, $line);
        });
        set_exception_handler(function ($exception)
        {
            return static::exception_handler($exception);
        });
        static::$initialized = true;
        return true;
    }
    private function set_message($message): void
    {
        $this->message = $message;
    }
    private function set_code($code): void
    {
        $this->code = $code;
    }
    private function set_file($file): void
    {
        $this->file = $file;
    }
    private function set_line($line): void
    {
        $this->line = $line;
    }

    protected static function error_handler($errno, $message, $file, $line): bool
    {
        // Only handle errors matching self::$errno
        if ($errno === self::$errno)
        {
            $error = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();
            if ($error instanceof static)
            {
                $error->set_message($message);
                $error->set_code($errno);
                $error->set_file($file);
                $error->set_line($line);
                throw $error;
            }
            echo self::format_message($message, $errno) . " in {$file}:{$line}\n";
            return true;
        }
        else
        {
            // Allow other handlers to process this error
            return false;
        }
    }
    protected static function exception_handler($exception): bool
    {
        // Ensure we're handling only instances of this Error class
        if ($exception instanceof static)
        {
            print self::format_message($exception->getMessage(), $exception->getCode()) .
                " in {$exception->getFile()}:{$exception->getLine()}\n";
            return true;
        }
        else
        {
            return false;
        }
    }
}
