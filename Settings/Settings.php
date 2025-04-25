<?php

namespace  PHP_Library\Settings;

use PHP_Library\CLIConsole\CLIConsoleTrait;

/**
 * Settings
 *
 * get Setting values from setting.ini file with Settings::get('section_name/key'). keys of the section [settings] can be accessed with 'key' directly.
 */
class Settings
{


    use CLIConsoleTrait;
    public static array $settings = [];
    private static bool $initialized = false;
    protected static $file_name = 'settings.ini';
    protected static string $main_section = 'settings';
    protected static array $template = [
        'settings' => [],
    ];

    /**
     * Echo the value of 'section/key'
     *
     * @param string $key key can be 'key' to access values of the section [settings] or 'section/key' to access values of other sections.
     * @param bool [optional] $strict if true, the method throws an error when the 'section/key' is not found.
     *
     * @return void
     */
    public static function echo(string $key, bool $strict = false): void
    {
        echo self::get(
            key: $key,
            strict: $strict
        );
    }

    /**
     * Get the value of 'section/key'
     *
     * @param string $key key can be 'key' to access values of the section [settings] or 'section/key' to access values of other sections.
     * @param bool $strict if true, the method throws an error when the 'section/key' is not found.
     *
     * @return mixed
     */
    public static function get(string $key, ?string $type_or_class = null, bool $strict = false): mixed
    {
        self::initialize();
        $section = self::$main_section;
        $key = self::get_section_key($key, $section);
        $setting_value = self::get_settings_value($section, $key, $strict);
        if ($type_or_class)
        {
            return self::cast_value($type_or_class, $setting_value);
        }
        return $setting_value;
    }

    /**
     * Register a Settings key-value pair
     *
     * @param string $key The 'key' or 'section/key' that should be added to the settings.ini file
     * @param null|bool|float|int|string [optional] $default the default value of that setting.
     *
     * @return bool true on success, false if settings already set.
     */
    public static function register(string $key, null|bool|float|int|string $default = ''): bool
    {
        self::initialize();

        if (strpbrk($key, '{}|&~![()^"'))
        {
            throw new \Error("'$key' must not contain any of this characters: {}|&~![()\"");
        }

        if (is_string($default))
        {
            $default = self::escape_characters($default);
        }

        $section = self::$main_section;
        $key = self::get_section_key($key, $section);

        if (!isset(self::$settings[$section]))
        {
            self::$settings[$section] = [];
        }

        if (!isset(self::$settings[$section][$key]))
        {
            self::$settings[$section][$key] = $default;
            self::write_current_settings_to_ini();
            return true;
        }
        return false;
    }

    /**
     * Creates settings.ini if not existing yet and loads the Content of settings.ini statically into Settings Class and sets self::$initialized.
     *
     * @return void
     */
    private static function initialize(): void
    {
        if (self::$initialized)
        {
            return;
        }
        if (!file_exists(filename: self::$file_name,))
        {
            self::touch_ini();
        }
        self::load();
        self::$initialized = true;
    }

    /**
     * Creates settings.ini file from array self::$template .
     * If settings.ini already exists, it will overwrite it.
     *
     * @return void
     */
    protected static function touch_ini(): void
    {
        self::write_ini(self::$template);
    }

    /**
     * Creates settings.ini file from current array self::$settings.
     * If settings.ini already exists, it will overwrite it.
     *
     * @return void
     */
    protected static function write_current_settings_to_ini(): void
    {
        self::write_ini(self::$settings);
    }

    /**
     * Creates settings.ini file from array $content array.
     * If settings.ini already exists, it will overwrite it.
     *
     * @param array $content The array of Settings. ['section' => ['key' => 'value'] ]
     *
     * @return void
     */
    private static function write_ini(array $content): void
    {
        $class = new \ReflectionClass(get_called_class());
        $comment_string = trim(preg_replace(
            [
                '/(\/\*\*| *\*\/$|^ *\*)/m',
                '/\n\n/m'
            ],
            [
                '',
                PHP_EOL
            ],
            $class->getDocComment()
        ));
        $settings_ini_content = '';
        foreach (explode(PHP_EOL, $comment_string) as $line)
        {
            $settings_ini_content .= "; " . trim($line) . PHP_EOL;
        }
        $settings_ini_content .= PHP_EOL;

        foreach ($content as $section_key => $section_items)
        {
            $settings_ini_content .= "[$section_key]" . PHP_EOL;
            foreach ($section_items as $key => $value)
            {
                switch ($type = gettype($value))
                {
                    case 'boolean':
                        $value = $value ? 'true' : 'false';
                        break;

                    case 'NULL':
                        $value = 'null';
                        break;

                    case 'string':
                        $value = '"' . self::escape_characters($value) . '"';
                        break;

                    case 'integer':
                    case 'double':
                        // No modification needed for these types
                        break;

                    default:
                        throw new \Error("Unsupported type: " . $type);
                }
                $settings_ini_content .= "{$key}={$value}" . PHP_EOL;
            }
            $settings_ini_content .= PHP_EOL;
        }
        if (file_put_contents(
            filename: self::$file_name,
            data: $settings_ini_content
        ) === false)
        {
            throw new \Error("Could not write data to '" . self::$file_name . "'");
        }
    }

    /**
     * Loads the settings.ini file.
     *
     * @return void
     */
    protected static function load(): void
    {
        self::$settings = parse_ini_file(
            filename: self::$file_name,
            process_sections: true,
            scanner_mode: INI_SCANNER_TYPED
        );
    }

    /**
     * Escapes character of a string to get used as ini value.
     *
     * @param string $string [explicite description]
     *
     * @return string
     */
    private static function escape_characters(string $string): string
    {
        return addcslashes($string, "\\\'\"\0\t\r\n;#=:");
    }

    /**
     *
     * @param string $key access subsections with 'section/key' or 'key' to acces main-section
     *
     * @return string
     */
    private static function get_section_key(string $key, &$section): string
    {
        $section_and_key = explode('/', $key, 2);
        switch (count($section_and_key))
        {
            case 1:
                return $section_and_key[0];
            case 2:
                $section = $section_and_key[0];
                return $section_and_key[1];
            default:
                throw new \Error("'$key' is an invalid key.");
        }
    }

    private static function get_settings_value(string $section, string $key, bool $strict): mixed
    {
        if (!isset(self::$settings[$section]))
        {
            if ($strict) throw new \Error("Setting section [{$section}] was not found.");
            return null;
        }
        if (!isset(self::$settings[$section][$key]))
        {
            if ($strict) throw new \Error("Setting {$key} in [{$section}] was not found.");
            return null;
        }

        return self::$settings[$section][$key];
    }

    private static function cast_value(string $type_or_class, mixed $value): mixed
    {
        if (class_exists($type_or_class))
        {
            return new $type_or_class($value);
        }
        elseif (settype($value, $type_or_class))
        {
        }
        else
        {
            throw new \Error("'{$type_or_class}' is not a type or class.");
        }
        return $value;
    }
}
