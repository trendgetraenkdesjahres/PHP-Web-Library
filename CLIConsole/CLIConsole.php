<?php

namespace  PHP_Library\CLIConsole;

use Dom\Document;
use DOMDocument;
use PHP_Library\ClassTraits\SingletonPattern;
use PHP_Library\CLIConsole\Command\Command;
use PHP_Library\CLIConsole\Error\Error;
use PHP_Library\Types\AbstractType;
use PHP_Library\Types\StringExtensions\AnsiString;
use PHP_Library\Types\StringType;

class CLIConsole
{

    use SingletonPattern;

    private static array $methods = [];

    private static array $method_aliases = [];


    private function __construct()
    {
        static::set_methods();
        static::set_method_aliases();
        readline_completion_function([static::class, 'completion_function']);
        register_shutdown_function([static::class, 'start_interface']);
    }

    public static function initiate()
    {
        static::init_singleton();
    }

    protected static function start_interface(): void
    {
        $input = '';
        while ($input !== 'exit')
        {
            $input = trim(readline('> '));
            $command = new Command($input);

            // find full name of alias
            if (false !== $result = array_search((string) $command, static::$method_aliases))
            {
                $command->set_command(static::$methods[$result]);
            }
            $reflection_method = $command->get_reflection();
            if ($reflection_method && $reflection_method->getNumberOfRequiredParameters() && empty($command->parameters))
            {
                echo static::get_comment($reflection_method) . "\n";
                foreach ($reflection_method->getParameters() as $parameter_reflection)
                {
                    echo static::get_parameter_type_string_formated($parameter_reflection);
                    $value = readline(" " . $parameter_reflection->getName() . "\t:");
                    $command->parameters[] = $value;
                }
                // draw help
            }
            try
            {
                $result = $command->execute();
                readline_add_history($command->input);
                static::print_result($result);
            }
            catch (\Error $e)
            {
                echo Error::get_emoji($e) . " " . $e->getMessage() . "\n";
            }
        }
    }

    protected static function set_methods(): void
    {
        foreach (get_declared_classes() as $class)
        {
            $reflection_class = new \ReflectionClass($class);
            foreach ($reflection_class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
            {
                if (! $method->isStatic())
                {
                    continue;
                }
                $method_name = $reflection_class->getName() . "::" . $method->getName();
                if (false !== array_search($method_name, static::$methods))
                {
                    continue;
                }
                static::$methods[] = $method_name;
            }
        }
    }
    protected static function set_method_aliases(): void
    {
        static::$method_aliases = static::get_unique_names(static::$methods);
    }

    private static function get_unique_names($methods, $depth = 1): array
    {
        $names = [];
        foreach ($methods as $method)
        {
            $parts = explode('\\', trim($method, '\\'));
            $key = implode('\\', array_slice($parts, -$depth)); // Get last $depth parts
            $names[$key][] = $method;
        }
        foreach ($names as $key => $group)
        {
            if (count($group) > 1)
            {
                return static::get_unique_names($methods, $depth + 1); // Increase depth if conflicts exist
            }
        }
        return static::$method_aliases = array_keys($names);
    }

    private static function completion_function($input, $index): array
    {
        if (mb_strlen($input) <= 3)
        {
            return [];
        }
        $matches = [];
        $suggestions = array_merge(
            readline_list_history(),
            get_defined_functions()['user'],
            get_defined_functions()['internal'],
            static::$method_aliases,
            static::$methods
        );
        foreach ($suggestions as $suggestion)
        {
            $suggestions_in_length = substr($suggestion, 0, mb_strlen($input));
            if (0 === strcasecmp($suggestions_in_length, readline_info("line_buffer")))
            {
                $matches[] = mb_substr($suggestion, $index);
            }
        }
        return $matches;
    }

    protected static function print_result(mixed $result): void
    {
        $variable = AbstractType::create_implementation($result);
        echo $variable->get_string_representation(true);
        echo "\n";
    }



    private static function get_parameter_type_string_formated(\ReflectionParameter $parameter): string
    {
        $string = $parameter->allowsNull() ? "?" : "";
        $type = $parameter->getType();
        return (new AnsiString($string . $type->getName()))->format_italic();
    }

    static function get_comment(\ReflectionFunctionAbstract $reflection_function): string
    {
        $content = new StringType($reflection_function->getDocComment());
        $content->remove_substring('/*')->remove_substring('*/');
        $comment = [];
        foreach ($content->get_explode_array(PHP_EOL) as $line)
        {
            $line = new AnsiString($line);
            $line->trim("* ");
            if (!$line->__toString())
            {
                continue;
            }
            if (empty($comment))
            {
                $line->format_bold();
            }
            if ($line = $line->__toString())
            {
                $comment[] = $line;
            }
        }
        return implode(PHP_EOL, $comment);
    }
}
