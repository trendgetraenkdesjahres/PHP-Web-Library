<?php

namespace Settings;

use Notices\Notice;
use Notices\Warning;

/**
 * Settings
 *
 * get Setting from setting.ini file with Settings::get('key'). echo with Settings::echo('key').
 * creates file if not there yet.
 */
class Settings
{
    public static $settings = [];
    protected static $file_name = 'settings.ini';
    protected static array $template = [
        'settings' => [
            'cookie_file' => 'data/cookies.txt',
            'temporary_files_folder' => 'tmp/',
            'temporary_files_max_mb' => '64',
            'history_file' => 'data/history.json',
        ],

        'spotify' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_url' => '',
        ],

        'telegram' => [
            'token' => '',
            'chat_id' => '',
        ],

        'youtube' => [
            'api_key' => '',
        ],
    ];

    /**
     * Method get
     *
     * @param string $key key can be 'key' or even 'section/key' or further.
     *
     * @return string
     */
    public static function get(string $key, $required = false): ?string
    {
        if (!self::create_ini()) {
            self::load();

            $return = self::$settings;
            if ($keys = explode(separator: '/', string: $key)) {
                if (isset($return['settings'][$key])) {
                    return $return['settings'][$key];
                }

                foreach ($keys as $k) {
                    if (isset($return[$k])) {
                        $return = $return[$k];
                    } elseif ($required) {
                        throw new \Error("Setting '$key' was not found.");
                    } else {
                        $return = null;
                    }
                }
                return $return;
            } else {
                return self::$settings[$key];
            }
        } else {
            Notice::trigger(
                self::$file_name . " was not found. Created from template."
            );
            return null;
        }
    }

    /**
     * Method echo
     *
     * @param string $key key can be 'key' or even 'section/key' or further.
     *
     * @return void
     */
    public static function echo(string $key): void
    {
        echo self::get(key: $key);
    }

    /**
     * Method create_ini
     *
     * creates settings.ini file if possible and not existing yet.
     * returs true on success, false on error.
     *
     * @return bool
     */
    protected static function create_ini(): bool
    {
        if (file_exists(filename: self::$file_name,)) {
            return false;
        }

        $settings_ini_content = '';
        foreach (self::$template as $section_key => $section_items) {
            $settings_ini_content .= "[$section_key]" . PHP_EOL;
            foreach ($section_items as $key => $value) {
                $settings_ini_content .= "{$key}='{$value}'" . PHP_EOL;
            }
        }
        return file_put_contents(
            filename: self::$file_name,
            data: $settings_ini_content
        ) ? true : false;
    }

    /**
     * Method load
     *
     * loads the settings.ini file.
     *
     * @return void
     */
    protected static function load()
    {
        self::$settings = parse_ini_file(
            filename: self::$file_name,
            process_sections: true,
            scanner_mode: INI_SCANNER_TYPED
        );
    }
}
