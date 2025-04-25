<?php

namespace PHP_Library\Types\StringRepresentation;

use PHP_Library\Types\AbstractType;
use PHP_Library\Types\ArrayType;
use PHP_Library\Types\StringType;

trait ArrayTypeStringRepresentationTrait
{
    use PrimitiveTypeStringRepresentationTrait;

    protected static string $open_expanded_value = "[";
    protected static string $close_expanded_value = "]";

    protected static string $key_value_arrow = "=>";

    abstract protected  function get_key_cells(): array;

    protected static function get_primitive_value(ArrayType $object): string
    {
        return $object->count();
    }

    protected static function get_html_string_representation(ArrayType $object, ?int $max_width = null): string
    {
        return
            static::get_primitive_html_string_representation($object, $max_width) .
            " " .
            nl2br(static::get_expanded_array_value($object, $max_width));
    }

    protected static function get_ansi_string_representation(ArrayType $object, ?int $max_width = null): string
    {
        return
            static::get_primitive_ansi_string_representation($object, $max_width) .
            " " .
            static::get_expanded_array_value($object, $max_width);
    }

    protected static function get_expanded_array_value(ArrayType $object, ?int $max_width = null): string
    {
        if (! static::get_primitive_value($object))
        {
            return static::$open_expanded_value . static::$close_expanded_value;
        }
        $key_column_width = static::get_column_key_width($object->get_key_cells());
        $value_column_width = static::get_column_value_width($max_width, $key_column_width);
        $expanded_array_string = static::$open_expanded_value . PHP_EOL;
        foreach ($object as $key => $value)
        {
            $expanded_array_string .= static::element_to_string_representation($key, $value, $key_column_width, $value_column_width) . PHP_EOL;
        }
        return $expanded_array_string . static::$close_expanded_value;
    }

    protected static function element_to_string_representation(string|int $key, mixed $value, int $key_column_width, int $value_column_width): string
    {
        $key = static::get_column_key_paded($key, $key_column_width);
        $element = AbstractType::create_implementation($value);
        $element_representation = $element->get_string_representation(true, $value_column_width);
        $representation_lines = explode(PHP_EOL, $element_representation);


        // if elements' value is expanded into 1 line
        if (count($representation_lines) ==  1)
        {

            return $key . static::$key_value_arrow . " " . $representation_lines[0];
        }

        // if elements' value is expanded into multiple lines
        foreach ($representation_lines as $i => $line)
        {
            $representation_lines[$i] = str_repeat(" ", $key_column_width + strlen(static::$key_value_arrow) + 1) . $line;
        }
        return $key . static::$key_value_arrow . " " . trim(implode(PHP_EOL, $representation_lines));
    }

    protected static function get_column_key_paded(int|string $key, int $key_column_width): string
    {
        $key = new StringType($key . " ");
        return $key->pad_right(
            $key_column_width,
            mb_substr(static::$key_value_arrow, 0, 1)
        );
    }

    protected static function get_column_key_width(array $key_column): int
    {
        if (!$key_column)
        {
            return 0;
        }
        foreach ($key_column as $key_cell)
        {
            $key_column_width = (! isset($key_column_width)) || strlen($key_cell) > $key_column_width
                ? strlen($key_cell)
                : $key_column_width;
        }
        return $key_column_width + 1;
    }

    protected static function get_column_value_width(int $total_max_width, $key_column_width): int
    {
        return max($total_max_width - $key_column_width - strlen(static::$key_value_arrow) - 1, 0);
    }
}
