<?php

namespace  PHP_Library\Types;

use Traversable;
use ArrayIterator;

/**
 * ObjectType represents an object manipulation class designed to provide common operations on objects.
 * It allows checking and accessing object properties, converting objects to strings, and more.
 *
 * @package TypeWrap
 */
class ObjectType extends TypeWrap implements \IteratorAggregate
{
    /**
     * Constructor.
     *
     * @param mixed $value The initial object to wrap.
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Check if a property exists on the wrapped object.
     *
     * @param int|string $property The property to check.
     * @return bool True if the property exists, false otherwise.
     */
    public function is_set(int|string $property): bool
    {
        return isset($this->value->$property);
    }

    /**
     * Get the value of this wrapped object property.
     *
     * @param int|string $property The property to retrieve.
     * @return mixed The value of the property.
     */
    public function get(int|string $property): mixed
    {
        if ($this->is_set($property)) {
            return $this->value->$property;
        }
    }

    /**
     * Get the class name of the wrapped object.
     *
     * @return StringType The class name as a StringType.
     */
    public function get_class_name(): StringType
    {
        return new StringType(get_class($this->value));
    }

    /**
     * Set the value of an object property.
     *
     * @param int|string $property The property to set.
     * @param mixed $value The value to assign to the property.
     * @return ObjectType This ObjectType instance.
     */
    public function set(int|string $property, mixed $value): ObjectType
    {
        if ($this->is_set($property)) {
            $this->value->$property = $value;
        }
        return $this;
    }

    /**
     * Convert the object to a string representation.
     *
     * @return string The object properties as a string.
     */
    public function __toString(): string
    {
        $class_name = $this->get_class_name();
        $show_scope = true;

        $string = new StringType();
        $properties = get_mangled_object_vars($this->value);
        if (empty($properties)) {
            $empty = new StringType('empty');
            return $empty->ansi_format_italic();
        }
        foreach ($properties as $property => $value) {
            $property_name = new StringType($property);
            $property_name->sanitize();
            if ($property_name->is_starting_with($class_name)) {
                $scope = 'private';
                $property_name->remove_string_at_start($class_name);
            } else if ($property_name->is_starting_with("*")) {
                $scope = 'protected';
                $property_name->remove_string_at_start("*");
            } else {
                $scope = 'public';
            }
            $string->append_line($show_scope
                ? "-($scope)->$property_name = "
                : "->$property_name = ");
        }

        /* append line of second column to each line */
        $longest_line_length = $string->get_longest_line()->get_length();
        $indexed_properties = array_keys($properties);
        $output = new StringType();
        foreach ($string->get_lines() as $i => $line) {
            $property = $indexed_properties[$i];
            $value = Type::construct($properties[$property]);
            if (is_string($value->value)) {
                $value = new StringType((string)$value);
                $string_length = $value->get_length();
                $value->replace(PHP_EOL, '¶' . PHP_EOL)->word_wrap(80)->surround('`')->append("($string_length)");
            } else {
                $value = new StringType((string) $value);
            }


            if ($value->has_linebreak()) {
                $value->padding_left($longest_line_length, 1);
            }

            $output->append_line(
                $line->padding_right(
                    $longest_line_length - $line->get_length()
                )->append($value)
            );
        }
        if (isset($GLOBALS["DEBUG_PRINT"])) {
            $output->box_around_string(1, $class_name);
        }
        return $output->value;
    }

    /**
     * Get an iterator for the object properties.
     *
     * @return Traversable An iterator for the object properties.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->value);
    }
}