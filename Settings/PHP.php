<?php

namespace PHP_Library\Settings;

use PHP_Library\CLIConsole\CLIConsoleClassTrait;
use PHP_Library\CLIConsole\Error\Error;

/**
 * Wrapper around PHP ini settings with CLIConsole integration.
 *
 * Provides safe access and modification of settings with autocomplete support.
 */
class PHP
{
    use CLIConsoleClassTrait;

    /** @var array<string,mixed> Cached list of accessible PHP ini settings */
    protected static array $accessable_settings;

    /** @var bool Initialization guard */
    private static bool $initialized = false;

    /**
     * Ensures initialization of settings on construction.
     */
    final public function __construct()
    {
        static::initialize();
    }

    /**
     * Get the current value of a PHP ini setting.
     *
     * @param string $key Ini setting name
     * @return string|false Returns setting value or false if not set
     *
     * @example
     *   PHP::get("memory_limit"); // "128M"
     */
    public static function get(string $key): string|false
    {
        static::initialize();
        $value = ini_get($key);
        return ($value === false || $value === "") ? false : $value;
    }

    /**
     * Set a PHP ini setting, validating accessibility and registration.
     *
     * @param string $key Setting name
     * @param mixed $value Setting value
     * @return string The new ini value
     *
     * @throws Error If the setting is not valid, not accessible, or could not be modified
     *
     * @example
     *   PHP::set("display_errors", "1"); // "1"
     */
    public static function set(string $key, $value): string
    {
        static::initialize();

        if (!key_exists($key, ini_get_all(details: false))) {
            throw new Error("\"$key\" is not a name for a setting.");
        }
        if (!key_exists($key, static::get_accessable_settings())) {
            throw new Error("\"$key\" is inaccessable.");
        }
        if (!ini_set($key, $value)) {
            throw new Error("Could not modify \"$key\" to $value.");
        }

        if (!Settings::register("PHP/$key", $value)) {
            Settings::set("PHP/$key", $value);
        }

        return ini_get($key);
    }

    /**
     * Get all PHP ini settings that are user-accessible (INI_USER or INI_ALL).
     *
     * @return array<string,mixed>
     *
     * @example
     *   $settings = PHP::get_accessable_settings();
     *   echo $settings["memory_limit"]; // "128M"
     */
    public static function get_accessable_settings(): array
    {
        static::initialize();

        if (!isset(static::$accessable_settings)) {
            foreach (ini_get_all() as $key => $settings) {
                if ($settings['access'] == \INI_USER || $settings['access'] == \INI_ALL) {
                    static::$accessable_settings[$key] = $settings['local_value'] ?? null;
                }
            }
        }

        return static::$accessable_settings ?? [];
    }

    /**
     * Return autocomplete suggestions for CLIConsole integration.
     *
     * Provides option names for `get` and `set` commands.
     */
    protected static function get_autocompletition(): array
    {
        $option_names = array_keys(static::get_accessable_settings());
        return [
            'set' => [0 => $option_names],
            'get' => [0 => $option_names],
        ];
    }

    /**
     * Initialize PHP settings by loading from the Settings file.
     */
    private static function initialize(): void
    {
        if (static::$initialized) {
            return;
        }

        $settings = parse_ini_file(
            filename: Settings::$file_name,
            process_sections: true,
            scanner_mode: INI_SCANNER_TYPED
        );

        if (isset($settings['PHP'])) {
            foreach ($settings['PHP'] as $key => $value) {
                ini_set($key, $value);
            }
        }

        static::$initialized = true;
    }
}

require_once "load_php_settings.php";
