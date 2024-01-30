<?php

namespace Library\Types;

/**
 * ArrayType represents a class for handling array values.
 *
 * @package TypeWrap
 */
class ArrayType extends TypeWrap implements \Iterator
{
    /**
     * Constructor to initialize the ArrayType with a given array value.
     *
     * @param mixed $value The initial array value.
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Get the value associated with a specific key in the array.
     *
     * @param int|string $key  The key to get the value for.
     * @return mixed The value associated with the key.otherwise void.
     */
    public function get(int|string $key): mixed
    {
        if ($this->is_set($key)) {
            return $this->value[$key];
        }
    }

    /**
     * Check if a specific key exists in the array.
     *
     * @param int|string $key The key to check
     * @return mixed True if the key exists, otherwise false.
     */
    public function is_set(int|string $key): bool
    {
        return isset($this->value[$key]);
    }

    /**
     * Set a value for a specific key in the array.
     *
     * @param int|string $key The key to set the value for.
     * @param mixed $value The value to set.
     * @return ArrayType The modified ArrayType.
     */
    public function set(int|string $key, mixed $value): ArrayType
    {
        $this->value[$key] = $value;
        return $this;
    }

    /**
     * Move the value associated with one key to another key in the array.
     *
     * @param int|string $from_key The source key to move the value from.
     * @param int|string $to_key The destination key to move the value to.
     * @return ArrayType The modified ArrayType.
     */
    public function move_value(int|string $from_key, int|string $to_key): ArrayType
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
            if ($key->is_type('string')) {
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
            if ($key->is_type('string')) {
                $key->surround("'");
            }
            $seperator->repeat(
                ($longest_key_string_length + 1)
                    - $key->get_length()
            );
            if (is_object($value) && get_class($value) === 'Closure') {
                $string->append("$key $seperator> Closure" . PHP_EOL);
            } else {
                $value = new StringType((string) Type::construct($value));
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
}
