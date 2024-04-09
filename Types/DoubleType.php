<?php

namespace  PHP_Library\Types;

/**
 * DoubleType represents a class for handling integer values.
 *
 * @package TypeWrap
 */
class DoubleType extends TypeWrap
{
    /**
     * Convert an integer to its string representation.
     *
     * @return string The string representation of the integer.
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}