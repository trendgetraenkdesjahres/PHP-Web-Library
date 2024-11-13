<?php

namespace PHP_Library\Superglobals;

use PHP_Library\Error\Warning;
use PHP_Library\Superglobals\PHPTraits\RequestTrait;

class Post
{
    use RequestTrait;

    static mixed $content;

    public static function has_content_type(string $name): bool
    {
        return $_SERVER['CONTENT_TYPE'] === $name;
    }

    public static function get_content_type(): string
    {
        return $_SERVER['CONTENT_TYPE'];
    }

    public static function get_content_field(string $key): mixed
    {
        if (! isset(static::$content)) {
            static::set_content();
        }
        if (!isset(static::$content[$key])) {
            Warning::trigger("Undefined Post Field '{$key}'");
            return null;
        }
        return static::$content[$key];
    }

    /**
     * Undocumented function
     *
     * @param string ...$key If multiple, it will check with AND-operator
     * @return boolean
     */
    public static function has_content_field(string ...$keys): bool
    {
        if (! isset(static::$content)) {
            static::set_content();
        }
        foreach ($keys as $key) {
            if (!isset(static::$content[$key])) {
                return false;
            }
        }
        return true;
    }

    protected static function set_content(): void
    {
        if (isset(static::$content)) {
            return;
        }

        if (self::get_content_type() === 'application/json') {
            $json = json_decode(file_get_contents("php://input"), true);
            static::$content = $json ? $json : [];
        }

        if (self::get_content_type() === 'multipart/form-data') {
            static::$content = array_merge($_POST, $_FILES);
        }
        static::$content = $_POST;
    }
}
