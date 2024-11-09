<?php

namespace PHP_Library\Superglobals;

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

    public static function extract_content(): int
    {
        $content = static::get_content();
        return extract($content);
    }

    public static function get_content(): array
    {
        if (isset(static::$content)) {
            return static::$content;
        }

        if (self::get_content_type() === 'application/json') {
            return static::$content = json_decode(file_get_contents("php://input"), true);
        }

        if (self::get_content_type() === 'multipart/form-data') {
            return static::$content = array_merge($_POST, $_FILES);
        }
        return static::$content = $_POST;
    }
}
