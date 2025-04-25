<?php

namespace  PHP_Library\Types;

use PHP_Library\Error\Error;
use TypeError;

abstract class AbstractType
{

    protected $value;

    abstract public function get_string_representation(bool $format_output = false, ?int $max_width = null): string;
    abstract protected function to_string(): string;
    abstract protected static function get_php_type(): string;
    abstract protected static function get_type_class_short_name(): string;



    /**
     * Constructor for Type Objects.
     *
     * @param $value The value of this.
     */
    final public function __construct($value)
    {
        if (! static::validate_type($value))
        {
            throw new TypeError("Value ({$value}) is not a " . static::get_type_class_short_name() . ".");
        }
        $this->value = $value;
    }
    final public function __toString(): string
    {
        return $this->to_string();
    }
    /**
     * Check if the custom type is of a specific type or class.
     *
     * @param string $type_or_class The type or class name to check against.
     * @return bool
     */
    public function is(string $type_or_class): bool
    {
        return ($type_or_class == static::get_php_type());
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

    public function get_value(): mixed
    {
        return $this->value;
    }


    final static public function create_implementation(mixed $value): static
    {
        if ($value === UninitializedType::SYMBOL)
        {
            return new UninitializedType(false);
        }
        switch (gettype($value))
        {
            case 'boolean':
                return new BooleanType($value);
            case 'integer':
                return new IntegerType($value);
            case 'float':
            case 'double':
                return new FloatType($value);
            case 'string':
                return new StringType($value);
            case 'array':
                return new ArrayType($value);
            case 'object':
                return new ObjectType($value);
            case 'null':
            case 'NULL':
                return new NullType($value);
            default:
                throw new Error(gettype($value) . "???");
        }
    }

    protected static function validate_type($value): bool
    {
        return  gettype($value) == static::get_php_type();
    }
}
