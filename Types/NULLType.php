<?php

namespace  PHP_Library\Types;

/**
 * NULLType represents a class for handling NULL values.
 *
 * @package TypeWrap
 */
class NULLType extends TypeWrap
{
    /**
     * Convert NULL to a string representation.
     *
     * @return string A formatted string representation of NULL.
     */
    public function __toString(): string
    {
        // Create a StringType instance with "null" and apply ANSI italic formatting.
        return (new Str("null"))->format_italic()->__toString();
    }
}
