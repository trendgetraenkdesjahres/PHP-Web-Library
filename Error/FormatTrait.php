<?php

namespace PHP_Library\Error;

trait FormatTrait
{
    public function __toString(): string
    {
        return self::format_message($this->message);
    }

    protected static function format_message($message, ?int $code = null)
    {
        $code = is_int($code) ? "({$code})" : '';
        $class_name = (new \ReflectionClass(__CLASS__))->getShortName();
        $function_trace = self::get_parent_functions(trace_limit: 3);
        return "{$class_name}{$code} [{$function_trace}]: {$message}";
    }

    /**
     * Get the string of the calling function.
     *
     * @param int $trace_limit The number of call stack levels to trace.
     *
     * @return string The name of the calling function.
     */
    protected static function get_parent_functions(int $trace_limit): string
    {
        $trace = array_reverse(debug_backtrace(
            options: 2,
            limit: $trace_limit
        ));
        $function_name = '';
        $open_brackets = 0;
        foreach ($trace as $caller) {
            if (isset($caller['type'])) {
                if ($caller['class'] === get_called_class() || $caller['class'] === get_called_class()) {
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
    protected static function get_parent_file(int $trace_level): string
    {
        $trace = debug_backtrace(
            options: 2,
            limit: $trace_level
        )[$trace_level - 1];
        $file = str_replace(
            search: getcwd() . "/",
            replace: '',
            subject: $trace['file']
        );
        return "$file:{$trace['line']}";
    }
}
