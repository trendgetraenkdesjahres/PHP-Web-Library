<?php

namespace  PHP_Library\Types;

abstract class AbstractType
{

    protected $value;

    /**
     * Constructor for TypeWrap.
     *
     * @param mixed $value The value to wrap with a custom type.
     */
    abstract public function __construct($value);

    /**
     * Convert the custom type to a string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return var_export($this->value);
    }

    /**
     * Get the type of the custom type.
     *
     * @return string
     */
    public function get_type(): string
    {
        return ($type = gettype($this->value)) === 'object' ? get_class($this->value) : $type;
    }

    /**
     * Check if the custom type is of a specific type or class.
     *
     * @param string $type_or_class The type or class name to check against.
     * @return bool
     */
    public function is(string $type_or_class): bool
    {
        return ($type_or_class == $this->get_type());
    }

    /**
     * Check if the variable is empty
     *
     * @return bool
     */
    public function is_empty(): bool
    {
        return empty($this->value);
    }

    public function get_built_in_type(): mixed
    {
        return $this->value;
    }
}
