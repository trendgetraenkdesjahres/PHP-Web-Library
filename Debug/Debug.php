<?php

namespace Debug;

use Types\StringType;
use Types\Type;

class Debug
{
    private string $method;
    private string $location;

    private string $expression_is_object;
    private string $expression_type;
    private mixed  $expression_value;

    public function __construct(mixed $expression)
    {
        $caller = debug_backtrace(limit: 2)[1];
        $caller_location = str_replace(
            search: getcwd() . "/",
            replace: '',
            subject: $caller['file']
        );
        $this->method   = $caller['class'] . $caller['type'] . $caller['function'];
        $this->location = $caller_location . ":{$caller['line']}";

        $this->expression_type      = ($type = gettype($expression)) == 'object' ? get_class($expression) : $type;
        $this->expression_value     = $expression;
    }

    public static function die(mixed $var = null): void
    {
        $debugger = new Debug($var);
        $debugger->print_expr_info();
        die();
    }

    public static function var(mixed $var = null): Debug
    {
        $debugger = new Debug($var);
        $debugger->print_expr_info();
        return $debugger;
    }

    private function print_expr_info(): void
    {
        $GLOBALS['DEBUG_PRINT'] = true;
        if (php_sapi_name() === 'cli') {
            $this->print_cli_var();
        } else {
            $this->print_html_var();
        }
        unset($GLOBALS['DEBUG_PRINT']);
    }

    private function print_cli_var(): void
    {
        $var_content = Type::construct($this->expression_value);
        $title = new StringType("$this->method($this->expression_type)");
        if ($this->expression_type == 'string') {
            $expression = new StringType((string)$var_content);
            $string_length = $expression->get_length();
            $expression->replace(PHP_EOL, '¶' . PHP_EOL)->word_wrap(80)->surround('`')->append("($string_length)");
        } else {
            $expression = new StringType((string) $var_content);
        }
        $title->ansi_format_bold();
        $string = new StringType("$title $this->location\n$expression");
        print $string->box_around_string(2);
    }

    private function print_html_var(): void
    {
        print_r("<pre>" . var_export($this->expression_value, true) . "</pre>");
    }
}
