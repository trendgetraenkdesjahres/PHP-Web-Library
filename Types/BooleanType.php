<?php

namespace Types;

/**
 * BooleanType represents a class for handling boolean values.
 *
 * @package TypeWrap
 */
class BooleanType extends TypeWrap
{
    /**
     * Convert a boolean value to its string representation ("true" or "false").
     *
     * @return string The string representation of the boolean value.
     */
    public function __toString(): string
    {
        // Create a StringType with "true" if the value is true, otherwise "false".
        $string = new StringType($this->value ? "true" : "false");
        // Apply ANSI italic formatting and return the formatted string.
        return (string) $string->ansi_format_italic();
    }
}
