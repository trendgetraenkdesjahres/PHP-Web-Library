<?php

namespace  PHP_Library\Debug;

use  PHP_Library\Types\Str;
use  PHP_Library\Types\Type;

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
        $class = (new \ReflectionClass($caller['class']))->getShortName();
        $this->method   = $class . $caller['type'] . $caller['function'];
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
        $title = new Str("$this->method($this->expression_type)");
        if ($this->expression_type == 'string') {
            $expression = new Str((string)$var_content);
            $string_length = $expression->get_length();
            $expression->replace(PHP_EOL, '¶' . PHP_EOL)->word_wrap(80)->surround('`')->append("($string_length)");
        } else {
            $expression = new Str((string) $var_content);
        }
        $title->format_bold();
        $string = new Str("$title $this->location\n$expression");
        print $string->box_around_string(2);
    }

    private function print_html_var(): void
    {
        $var_content = Type::construct($this->expression_value);
        $title = new Str("$this->method(<code>$this->expression_type</code>)");
        if ($this->expression_type == 'string') {
            $expression = new Str((string)$var_content);
            $string_length = $expression->get_length();
            $expression->replace(PHP_EOL, '¶' . PHP_EOL)->word_wrap(80)->surround('`')->append("($string_length)");
        } else {
            $expression = new Str((string) $var_content);
        }
        $css_style = "
        details {
            position: sticky;
            width: max-content;
            padding: 0.5em 1em;
        }

        details summary > * {
            display: inline-block;
        }

        details summary {
            cursor: pointer;
        }

        details figure {
            background-color: rgba(0,0,0, 0.05);
            margin: 0;
            padding: 0.5em;
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 500ms;
        }

        details[open] figure {
            grid-template-rows: 1fr;
        }

        details figure pre {
            overflow: hidden;
            margin: 0;
        }
        ";
        print "<style>$css_style</style><details><summary><div>$this->location<b> $title</b></div></summary><figure><pre>$expression</pre><figcaption><small></small></figcaption></figure></details>";
    }
}
