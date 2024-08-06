<?php

namespace PHP_Library;

class Autoload
{
    private static string $namespace = __NAMESPACE__;

    private static string $includes_dir = '';
    private static string $framework_dir = '/framework/';

    public static function init()
    {
        spl_autoload_register(function ($class) {
            if (!self::is_class_in_namespace($class)) {
                $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
                include self::$includes_dir . $class . '.php';
                return false;
            }
            $file = self::get_framework_filename($class);
            return self::soft_require($file);
        });
    }

    public static function is_class_in_namespace($full_class_name): string
    {
        return is_int(strpos($full_class_name, self::$namespace));
    }

    private static function get_framework_filename($full_class_name): string
    {
        $full_class_name = substr($full_class_name, strlen(self::$namespace . "\\"));
        return self::$framework_dir . str_replace('\\', DIRECTORY_SEPARATOR, $full_class_name) . '.php';
    }

    private static function soft_require($php_file): string
    {
        if (file_exists($php_file)) {
            require $php_file;
            return true;
        }
        return false;
    }
}

Autoload::init();
