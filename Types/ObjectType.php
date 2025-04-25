<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\StringRepresentation\ObjectTypeStringRepresentationTrait;

/**
 * ObjectType represents an object manipulation class designed to provide common operations on objects.
 * It allows checking and accessing object properties, converting objects to strings, and more.
 *
 * @package TypeWrap
 */
class ObjectType extends AbstractType
{
    use ObjectTypeStringRepresentationTrait;

    protected function get_key_cells(): array
    {
        $key_cells = [];
        $reflection_class = new \ReflectionClass($this->value);
        foreach ($reflection_class->getProperties() as $property)
        {
            $cell = "!";
            if ($property->isProtected())
            {
                $cell = ".";
            }
            if ($property->isPublic())
            {
                $cell = " ";
            }
            $key_cells[$property->getName()] = $cell . ($property->isStatic() ? "#" : ' ') . ($property->getType() ?? "?") . " $" . $property->getName();
        }
        return $key_cells;
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
        return $this->value->$property;
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
        if ($this->is_set($property))
        {
            $this->value->$property = $value;
        }
        return $this;
    }

    /**
     * Convert the array to its string representation with formatting.
     *
     * @return string The formatted string representation of the array.
     */
    protected function to_string(): string
    {
        return 'object';
    }

    /**
     * Convert the array to its string representation with formatting.
     *
     * @return string The formatted string representation of the array.
     */
    protected static function get_php_type(): string
    {
        return 'object';
    }
}
