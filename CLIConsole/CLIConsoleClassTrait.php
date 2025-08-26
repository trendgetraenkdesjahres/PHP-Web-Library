<?php

namespace PHP_Library\CLIConsole;

/**
 * Trait for classes that want to expose methods to the CLIConsole with
 * autocomplete support.
 *
 * Implementing classes must define `get_autocompletition()` which provides
 * argument suggestions for console use.
 *
 * @example
 *   // Example return structure for get_autocompletition():
 *   [
 *       'set' => [
 *           0 => ['display_errors', 'memory_limit'], // arg 0 suggestions
 *           1 => ['0', '1'],                         // arg 1 suggestions
 *       ],
 *       'get' => [
 *           0 => ['display_errors', 'memory_limit'],
 *       ],
 *   ]
 */
trait CLIConsoleClassTrait
{
    /**
     * Return autocomplete suggestions for CLIConsole.
     *
     * Keys are method names, values are arrays indexed by argument position.
     * Each entry is a list of possible values for that argument.
     *
     * @return array<string, array<int, array<int,string>>>
     */
    abstract public static function get_autocompletition(): array;
}
