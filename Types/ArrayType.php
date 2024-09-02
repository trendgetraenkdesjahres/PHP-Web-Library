<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\BaseTypeTraits\PHPArrayFunctions;

/**
 * ArrayType represents a class for handling array values.
 *
 * @package TypeWrap
 */
class ArrayType extends AbstractType implements \Iterator, \ArrayAccess, \Countable
{
    use PHPArrayFunctions;
    /**
     * Constructor to initialize the ArrayType with a given array value.
     *
     * @param mixed $value The initial array value.
     */
    public function __construct(mixed ...$value)
    {
        if (count($value) === 1 && key_exists(0, $value) && is_array($value[0])) {

            $this->value = $value[0];
        } else {
            $this->value = $value;
        }
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
        if (is_null($offset)) {
            $this->value[] = $value;
        } else {
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

    /**
     * Convert the array to its string representation with formatting.
     *
     * @return string The formatted string representation of the array.
     */
    public function __toString(): string
    {
        /* title */
        $title = new StringType("Array(" . count($this->value) . ")");
        if (count($this->value) === 0) {
            return $title;
        }

        /* keys formatieren und laenge vom laengsten key suchen */
        $longest_key_string_length = 0;
        foreach ($this->value as $key => $value) {
            $key = new StringType($key);
            if ($key->is('string')) {
                $key->surround("'");
            }
            if (($length = $key->get_length()) > $longest_key_string_length) {
                $longest_key_string_length = $length;
            }
        }

        $string = new StringType();
        foreach ($this->value as $key => $value) {
            $seperator = new StringType("\xE2\x95\x90");
            $key = new StringType($key);
            if ($key->is('string')) {
                $key->surround("'");
            }
            $seperator->repeat(
                ($longest_key_string_length + 1)
                    - $key->get_length()
            );
            if (is_object($value) && get_class($value) === 'Closure') {
                $string->append("$key $seperator> Closure" . PHP_EOL);
            } else {
                $value = new StringType((string) AbstractType::construct($value));
                if (is_string($value->value)) {
                    $value = new StringType((string)$value);
                    $string_length = $value->get_length();
                    $value->replace(PHP_EOL, '¶' . PHP_EOL)->word_wrap(80)->surround('`')->append("($string_length)");
                } else {
                    $value = new StringType((string) $value);
                }
                if ($value->has_linebreak()) {
                    $value->padding_left($longest_key_string_length + 1, 1);
                }
                $string->append_line("$key $seperator> $value");
            }
        }
        if (isset($GLOBALS["DEBUG_PRINT"])) {
            $string->box_around_string(1, $title);
        }
        return $string;
    }
}
