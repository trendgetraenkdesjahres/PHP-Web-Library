<?php

namespace PHP_Library\Types\StringRepresentation;

use PHP_Library\Types\StringExtensions\AnsiString;
use PHP_Library\Types\StringType;

trait StringTypeStringRepresentationTrait
{
    use PrimitiveTypeStringRepresentationTrait;

    protected static function get_primitive_value(StringType $object): string
    {
        return $object->get_length();
    }

    protected static function get_html_string_representation(StringType $object, ?int $max_width = null): string
    {
        return static::get_primitive_html_string_representation($object, $max_width) .
            nl2br(static::get_expanded_string_value($object, $max_width), false);
    }

    protected static function get_ansi_string_representation(StringType $object, ?int $max_width = null): string
    {
        return static::get_primitive_ansi_string_representation($object, $max_width) .
            static::get_expanded_string_value($object, $max_width);
    }

    protected static function get_expanded_string_value(StringType $object, ?int $max_width = null): string
    {
        $value = str_replace(PHP_EOL, "¶" . PHP_EOL, $object->value);
        $value = is_null($max_width) ? $value : wordwrap($value, $max_width, PHP_EOL, true);
        return is_int(strpos($value, PHP_EOL)) ? PHP_EOL . $value : " `{$value}`";
    }
}
