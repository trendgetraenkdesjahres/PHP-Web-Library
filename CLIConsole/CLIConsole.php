<?php

namespace PHP_Library\CLIConsole;

use PHP_Library\ClassTraits\SingletonPattern;
use PHP_Library\CLIConsole\Command\Command;
use PHP_Library\CLIConsole\Error\Error;
use PHP_Library\Settings\PHP;
use PHP_Library\Types\AbstractType;
use PHP_Library\Types\StringExtensions\AnsiString;
use PHP_Library\Types\StringType;

/**
 * Interactive command-line console with command discovery,
 * history, autocompletion, and reflection-based parameter input.
 */
class CLIConsole
{
    use SingletonPattern;

    /** @var array<string> Fully-qualified static method names available in the console */
    private static array $methods = [];

    /** @var string Path to readline history file */
    protected static string $history_file = ".cli_history";

    /** @var array<string> Aliases for static methods, resolved to avoid conflicts */
    private static array $method_aliases = [];

    /**
     * Console startup: registers readline, completion, history, and shutdown handler.
     */
    private function __construct()
    {
        static::set_methods();
        static::set_method_aliases();

        readline_read_history(static::$history_file);
        readline_completion_function([static::class, 'completion_function']);
        register_shutdown_function([static::class, 'start_interface']);
    }

    /**
     * Initiates the CLIConsole singleton and ensures history file exists.
     */
    public static function initiate(): void
    {
        new PHP;
        if (!file_exists(static::$history_file)) {
            touch(static::$history_file);
        }
        static::init_singleton();
    }

    /**
     * Interactive loop. Reads user input, resolves commands,
     * executes, and prints results until "exit".
     */
    protected static function start_interface(): void
    {
        $input = '';
        while ($input !== 'exit') {
            $input = trim(readline('> '));
            $command = new Command($input);

            // Resolve alias to fully qualified method
            if (false !== $result = array_search((string) $command, static::$method_aliases)) {
                $command->set_command(static::$methods[$result]);
            }

            $reflection_method = $command->get_reflection();

            // If method requires parameters and none were supplied, prompt interactively
            if ($reflection_method && $reflection_method->getNumberOfRequiredParameters() && empty($command->parameters)) {
                $comment = static::get_comment($reflection_method);
                echo $comment ? $comment . "\n" : "";

                foreach ($reflection_method->getParameters() as $parameter_reflection) {
                    echo static::get_parameter_type_string_formated($parameter_reflection);
                    $value = readline("$" . $parameter_reflection->getName() . " = ");
                    $command->parameters[] = $value;
                }
            }

            try {
                $result = $command->execute();
                readline_add_history($command->input);
                readline_write_history(static::$history_file);
                static::print_result($result);
            } catch (\Error $e) {
                echo Error::get_emoji($e) . " " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Discover all public static methods from declared classes
     * and register them for autocompletion and execution.
     *
     * If a class implements `CLIConsoleClassTrait`, it may also
     * return autocomplete values for its methods.
     */
    protected static function set_methods(): void
    {
        foreach (get_declared_classes() as $class) {
            $reflection_class = new \ReflectionClass($class);

            foreach ($reflection_class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (!$method->isStatic()) {
                    continue;
                }

                $method_name = $reflection_class->getName() . "::" . $method->getName();

                if (false !== array_search($method_name, static::$methods)) {
                    continue;
                }

                // Autocomplete extension if trait is present
                if (in_array(CLIConsoleClassTrait::class, $reflection_class->getTraitNames())) {
                    $method_value_autocomplete = $reflection_class->getMethod('get_autocompletition')->invoke(null);

                    if (key_exists($method->getName(), $method_value_autocomplete)) {
                        $suggestions = array_map(function ($value) use ($method_name) {
                            return $method_name . " " . (string) $value;
                        }, $method_value_autocomplete[$method->getName()][0]);

                        array_push(static::$methods, ...$suggestions);
                    }
                }

                static::$methods[] = $method_name;
            }
        }
    }

    /**
     * Build unique aliases for all registered static methods,
     * trimming namespaces until no conflicts remain.
     */
    protected static function set_method_aliases(): void
    {
        static::$method_aliases = static::get_unique_names(static::$methods);
    }

    /**
     * Completion function used by readline.
     *
     * Matches against history, defined functions, and known commands.
     */
    private static function completion_function($input, $index): array
    {
        if (mb_strlen($input) <= 1) {
            return [];
        }

        $matches = [];
        $suggestions = array_merge(
            function_exists("readline_list_history") ? readline_list_history() : [],
            get_defined_functions()['user'],
            get_defined_functions()['internal'],
            static::$method_aliases,
            static::$methods
        );

        foreach ($suggestions as $suggestion) {
            $suggestion_prefix = substr($suggestion, 0, mb_strlen($input));

            if (0 === strcasecmp($suggestion_prefix, readline_info("line_buffer"))) {
                $matches[] = mb_substr($suggestion, $index);
            }
        }

        return array_unique($matches);
    }

    /**
     * Print result from executed command with type formatting.
     *
     * @param mixed $result Result of executed command
     */
    protected static function print_result(mixed $result): void
    {
        $variable = AbstractType::create_implementation($result);
        echo $variable->get_string_representation(true) . "\n";
    }

    /**
     * Resolve minimal unique names for methods by trimming namespaces.
     *
     * Example:
     *   Input: ["App\\Utils\\Foo::bar", "App\\Models\\Foo::bar"]
     *   Step 1: ["Foo::bar", "Foo::bar"] (conflict)
     *   Step 2: ["Utils\\Foo::bar", "Models\\Foo::bar"] (resolved)
     *
     * @param array<string> $methods
     * @param int $depth Namespace depth used to resolve conflicts
     * @return array<string>
     */
    private static function get_unique_names(array $methods, int $depth = 1): array
    {
        $names = [];

        foreach ($methods as $method) {
            $parts = explode('\\', trim($method, '\\'));
            $key = implode('\\', array_slice($parts, -$depth));
            $names[$key][] = $method;
        }

        foreach ($names as $group) {
            if (count($group) > 1) {
                // Conflict: increase depth until unique
                return static::get_unique_names($methods, $depth + 1);
            }
        }

        return static::$method_aliases = array_keys($names);
    }

    /**
     * Format a reflection parameter type string for display.
     *
     * @example
     *   Input: parameter with type `?int $foo`
     *   Output: italicized "int" with "?" prefix if nullable
     */
    private static function get_parameter_type_string_formated(\ReflectionParameter $parameter): string
    {
        $string = $parameter->allowsNull() ? "?" : "";
        $type_reflection = $parameter->getType() ;
        if(!$type_reflection) {
            return "";
        }
        return (new AnsiString($string . $type_reflection->getName()))->format_italic()." ";
    }

    /**
     * Extract docblock comment from a reflection function and format it.
     *
     * First non-empty line is bolded, remaining lines preserved.
     */
    static function get_comment(\ReflectionFunctionAbstract $reflection_function): string
    {
        $comment = $reflection_function->getDocComment();
        if (!$comment) {
            return "";
        }

        $content = new StringType($reflection_function->getDocComment());
        $content->remove_substring('/*')->remove_substring('*/');

        $comment_lines = [];
        foreach ($content->get_explode_array(PHP_EOL) as $line) {
            $line = new AnsiString($line);
            $line->trim("* ");

            if (!$line->__toString()) {
                continue;
            }

            if (empty($comment_lines)) {
                $line->format_bold();
            }

            if ($line = $line->__toString()) {
                $comment_lines[] = $line;
            }
        }

        return implode(PHP_EOL, $comment_lines);
    }
}
