<?php

namespace PHP_Library\Error;

use ReflectionClass;
use PHP_Library\Debug\Context;
use PSpell\Config;

/**
 * Class Error
 *
 * Custom error class extending PHP's built-in \Error, with enhanced handling.
 * Keeps error data minimal and defers contextual info extraction to display time.
 */
class Error extends \Error
{
    use MessageFormatTrait;

    /**
     * Emoji to prefix the error message.
     *
     * @var string
     */
    protected static string $emoji = '😱';

    /**
     * Tracks whether the custom handlers have been initialized.
     *
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Exit code to use on fatal errors (not currently used internally).
     *
     * @var int
     */
    protected static int $exit_code = 1;

    /**
     * PHP error level this class handles.
     *
     * @var int
     */
    protected static int $errno = E_ERROR;

    /**
     * Throws this error if the given condition is true.
     *
     * @param bool $condition Condition to check.
     * @param string $message Error message to throw.
     * @param int $code Optional error code.
     * @throws self
     */
    public static function if(bool $condition, string $message, int $code = 0)
    {
        if ($condition) {
            throw new self($message, $code);
        }
    }

    /**
     * Construct the error object.
     *
     * @param string $message Error message.
     * @param int $code Error code.
     * @param \Throwable|null $previous Previous exception, if any.
     */
    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
        self::initialize();
    }

    /**
     * Initialize custom error and exception handlers.
     *
     * @return bool True if initialization occurred or was already done.
     */
    final protected static function initialize(): bool
    {
        if (static::$initialized) {
            return true;
        }
        set_error_handler(function ($errno, $message, $file, $line) {
            return static::error_handler($errno, $message, $file, $line);
        });
        set_exception_handler(function ($exception) {
            return static::exception_handler($exception);
        });
        static::$initialized = true;
        return true;
    }

    /**
     * Handles errors matching this class's error level.
     * Converts matching errors into exceptions of this class.
     *
     * @param int $errno Error level.
     * @param string $message Error message.
     * @param string $file File where error occurred.
     * @param int $line Line number of error.
     * @return bool True if handled, false otherwise.
     * @throws self
     */
    protected static function error_handler($errno, $message, $file, $line): bool
    {
        if ($errno === self::$errno) {
            $context = new Context();
            print static::format_message(
                $message,
                $errno,
                $context->get_method(),
                $context->get_file(),
                $context->get_line()
            );
            return true;
        }
        return false;
    }

    /**
     * Handles uncaught exceptions of this error class.
     * Extracts context dynamically and formats output.
     *
     * @param \Throwable $exception Exception to handle.
     * @return bool True if handled, false otherwise.
     */
    protected static function exception_handler($exception): bool
    {
        if ($exception instanceof static) {
            $context = new Context(trace: $exception->getTrace());
            print static::format_message(
                $exception->getMessage(),
                $exception->getCode(),
                $context->get_method(),
                $context->get_file(),
                $context->get_line()
            );
            return true;
        }
        return false;
    }
}
