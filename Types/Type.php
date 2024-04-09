<?php

namespace  PHP_Library\Types;

/**
 * TypeInterface is an interface for custom types.
 */
interface TypeInterface
{

    /**
     * Constructor for TypeInterface.
     *
     * @param mixed $value The value to wrap with a custom type.
     */
    public function __construct($value);

    /**
     * Convert the custom type to a string.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get the type of the custom type.
     *
     * @return string
     */
    public function get_type(): string;

    /**
     * Check if the custom type is of a specific type or class.
     *
     * @param string $type_or_class The type or class name to check against.
     * @return bool
     */
    public function is_type(string $type_or_class): bool;
}


class Type
{
    /**
     * Type is a class for handling custom types.
     */

    private static bool $initiated = false;

    /**
     * Construct a TypeWrap based on the given value.
     *
     * @param mixed $value The value to wrap with a custom type.
     * @return TypeWrap|null The created TypeWrap instance or null if it can't be created.
     */
    public static function construct(mixed $value): ?TypeWrap
    {
        if (!self::$initiated) {
            self::initiate();
        }
        $type = 'Types\\' . ucfirst(gettype($value)) . 'Type';
        if (class_exists($type) && in_array(
            needle: 'Types\\TypeWrap',
            haystack: class_parents($type)
        )) {
            return new $type($value);
        } else {
            throw new \Error("Can't create TypeWrap '$type'.");
        }
    }


    public static function initiate()
    {
        $current_directory = dirname(__FILE__);
        $phpFiles = glob($current_directory . '/*Type.php');
        foreach ($phpFiles as $file) {
            require_once $file;
        }
        self::$initiated = true;
    }
}

/**
 * TypeWrap is a class that implements the TypeInterface for wrapping values with custom types.
 *
 * @package TypeWrap
 */
class TypeWrap implements TypeInterface
{

    public $value;

    /**
     * Constructor for TypeWrap.
     *
     * @param mixed $value The value to wrap with a custom type.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

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
    public function is_type(string $type_or_class): bool
    {
        if (class_exists($type_or_class)) {
            return $type_or_class == $this->get_type();
        }
        return $type_or_class == $this->get_type();
    }
}