<?php

namespace PHP_Library\DatabaseModel;

use PHP_Library\CLIConsole\CLIConsoleTrait;
use PHP_Library\Error\Error;
use PHP_Library\Superglobals\Post;

abstract class DatabaseModel
{
    use DatabaseModelTrait;

    public static function get_post_field(string $property_name): mixed
    {
        return static::get_singular_name() . "[" . $property_name . "]";
    }

    public static function get_post(string $property_name): mixed
    {
        try
        {
            return Post::get_content_field(static::get_singular_name())[$property_name];
        }
        catch (\Throwable $e)
        {
            throw new Error("No value for (" . static::get_class_name() . ") " . static::get_singular_name() . "->{$property_name} in POST data. ($e)");
        }
    }

    public static function get_singular_name(): string
    {
        $singular_name = static::get_class_name();
        if (str_ends_with($singular_name, "Model"))
        {
            $singular_name = substr($singular_name, 0, strlen($singular_name) - 5);
        }
        return $singular_name;
    }

    public static function get_plural_name(): string
    {
        return static::get_singular_name() . "s";
    }
}
