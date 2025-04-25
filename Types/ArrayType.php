<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\BaseTypeTraits\PHPArrayFunctions;
use PHP_Library\Types\StringRepresentation\ArrayTypeStringRepresentationTrait;

/**
 * ArrayType represents a class for handling array values.
 *
 * @package TypeWrap
 */
class ArrayType extends AbstractType implements \Iterator, \ArrayAccess, \Countable
{
    use PHPArrayFunctions;
    use ArrayTypeStringRepresentationTrait;


    /**
     * Constructor to initialize the ArrayType with a given array value.
     *
     * @param mixed $value The initial array value.
     */

    protected static function get_php_type(): string
    {
        return 'array';
    }

    public function offsetExists($offset): bool
    {
        return isset($this->value[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->value[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return isset($this->value[$offset]) ? $this->value[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset))
        {
            $this->value[] = $value;
        }
        else
        {
            $this->value[$offset] = $value;
        }
    }

    public function rewind(): void
    {
        reset($this->value);
    }
    public function reset(): void
    {
        reset($this->value);
    }
    public function current(): mixed
    {
        return current($this->value);
    }
    public function key(): mixed
    {
        return key($this->value);
    }
    public function next(): void
    {
        next($this->value);
    }
    public function valid(): bool
    {
        return key($this->value) !== null;
    }

    public function serialize()
    {
        return serialize($this->value);
    }
    public function unserialize($data)
    {
        $this->value = unserialize($data);
    }

    public function count(): int
    {
        return count($this->value);
    }

    /**
     * Move the value associated with one key to another key in the array.
     *
     * @param int|string $from_key The source key to move the value from.
     * @param int|string $to_key The destination key to move the value to.
     * @return static The modified ArrayType.
     */
    public function move_value(int|string $from_key, int|string $to_key): static
    {
        $this->value[$to_key] = $this->value[$from_key];
        unset($this->value['from_key']);
        return $this;
    }

    protected function get_key_cells(): array
    {
        $keys = $this->get_keys();
        array_walk($keys, function ($key, $value)
        {
            return $value . " ";
        });
        return $keys;
    }

    /**
     * Convert the array to its string representation with formatting.
     *
     * @return string The formatted string representation of the array.
     */
    protected function to_string(): string
    {
        return 'array';
    }
}
