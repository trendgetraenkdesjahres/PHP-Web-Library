<?php

namespace PHP_Library\Types\StringRepresentation;

use PHP_Library\Superglobals\Get;
use PHP_Library\Superglobals\Server;
use PHP_Library\Types\AbstractType;
use PHP_Library\Types\StringExtensions\AnsiString;

trait PrimitiveTypeStringRepresentationTrait
{
    private static string $uninitialzed_code = "\u{E666}";
    protected static function get_primitive_value(AbstractType $object): string
    {
        return (string) $object;
    }
    public function get_string_representation(bool $format_output = false, ?int $max_width = null): string
    {
        if ($format_output)
        {
            if (is_null($max_width))
            {
                $max_width = (int) exec('tput cols') ? exec('tput cols') :  80;
            }
            if (Server::is_cli())
            {
                return static::get_ansi_string_representation($this, $max_width);
            }
            if (false !== strpos((string) Get::get_http_header_field('Accept'), 'text/html'))
            {
                return static::get_html_string_representation($this, $max_width);
            }
        }
        return static::get_unformated_string_representation($this);
    }

    protected static function get_unformated_string_representation(AbstractType $object, ?int $max_width = null): string
    {
        return static::get_type_class_short_name() . "(" . (string) $object . ")";
    }
    protected static function get_type_class_short_name(): string
    {
        $class_name = (new \ReflectionClass(get_called_class()))->getShortName();
        return str_ends_with($class_name, "Type") ? substr($class_name, 0, length: -4) : $class_name;
    }
    protected static function get_html_string_representation(AbstractType $object, ?int $max_width = null): string
    {
        return static::get_primitive_html_string_representation($object, $max_width);
    }
    protected static function get_primitive_html_string_representation(AbstractType $object, ?int $max_width = null): string
    {
        if ("" === static::get_primitive_value($object))
        {
            return static::get_type_in_html_format();
        }
        return static::get_type_in_html_format() . "(<b>" . static::get_primitive_value_in_html_format($object) . "</b>)";
    }
    protected static function get_type_in_html_format(): string
    {
        return "<i>" . static::get_type_class_short_name() . "</i>";
    }

    protected static function get_primitive_value_in_html_format(AbstractType $object, ?string $override = null): string
    {
        return "<b>" . static::get_primitive_value($object) . "</b>";
    }

    protected static function get_ansi_string_representation(AbstractType $object, ?int $max_width = null): string
    {
        return static::get_primitive_ansi_string_representation($object, $max_width);
    }

    protected static function get_primitive_ansi_string_representation(AbstractType $object, ?int $max_width = null): string
    {
        if ("" === static::get_primitive_value($object))
        {
            return static::get_type_in_ansi_format() .  "";
        }
        return static::get_type_in_ansi_format() . static::get_primitive_value_in_ansi_format($object);
    }

    protected static function get_type_in_ansi_format(): string
    {
        return (new AnsiString(static::get_type_class_short_name()))->format_italic();
    }

    protected static function get_primitive_value_in_ansi_format(AbstractType $object, ?string $override = null): string
    {
        return "(" .  static::get_primitive_value($object) . ")";
    }
}
