<?php

namespace  PHP_Library\Notices;

class Notice extends \Exception
{
    /**
     * Notice class extends the base \Exception class and handles custom PHP_Library\Notices.
     */

    private static $initialized = false;

    /**
     * Constructor for the Notice class.
     *
     * @param string $message The message to be associated with this notice.
     */
    public function __construct(string $message)
    {
        if (!self::$initialized) {
            $this::initialize();
            self::$initialized = true;
        }
        parent::__construct($message);
    }

    /**
     * Initialize the custom error handler for handling user PHP_Library\Notices and PHP_Library\Warnings.
     * This method sets up a custom error handler for E_USER_WARNING and E_USER_NOTICE.
     */
    private static function initialize()
    {
        set_error_handler(
            function ($level, $message, $file, $line) {
                if ($level === E_USER_WARNING || $level === E_USER_NOTICE) {
                    echo "$message\n";
                    return true;
                }
                return (false);
            }
        );
    }

    /**
     * Get the name of the calling function.
     *
     * @param int $trace_limit The number of call stack levels to trace.
     *
     * @return string The name of the calling function.
     */
    protected static function getFunctionName(int $trace_limit): string
    {
        $trace = array_reverse(debug_backtrace(
            options: 2,
            limit: $trace_limit
        ));
        $function_name = '';
        $open_brackets = 0;
        foreach ($trace as $caller) {
            if (isset($caller['type'])) {
                if ($caller['class'] === get_class() || $caller['class'] === get_called_class()) {
                    continue;
                }
                $open_brackets++;
                $function_name .= $caller['class'] . $caller['type'] . $caller['function'] . "(";
            } elseif (!$caller) {
                $function_name .= '> ';
            } else {
                $open_brackets++;
                $function_name .= $caller['function'] . "(";
            }
        }
        $function_name .= str_repeat(string: ")", times: $open_brackets);
        $function_name = $function_name ? $function_name : 'MAIN';
        return $function_name;
    }

    /**
     * Get the file and line number where the function is called from.
     *
     * @param int $trace_level The call stack level to trace.
     *
     * @return string The file and line number of the calling function.
     */
    protected static function getFunctionFile(int $trace_level): string
    {
        $caller = debug_backtrace(
            options: 2,
            limit: $trace_level
        )[$trace_level - 1];
        $location = str_replace(
            search: getcwd() . "/",
            replace: '',
            subject: $caller['file']
        );
        return "$location:{$caller['line']}";
    }


    /**
     * Trigger a custom notice with a message and additional information.
     *
     * @param string $message The notice message to display.
     */
    public static function trigger(string $message)
    {
        $function_name = self::getFunctionName(trace_limit: 3);
        $display_message = get_called_class() . ": \t[$function_name] $message";
        trigger_error(
            message: $display_message,
            error_level: E_USER_NOTICE
        );
    }
}

require_once("Warning.php");
