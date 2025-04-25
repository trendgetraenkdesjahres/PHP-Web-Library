<?php

namespace  PHP_Library\Types;

use PHP_Library\Types\BaseTypeTraits\PHPStringFunctions;
use PHP_Library\Types\StringRepresentation\StringTypeStringRepresentationTrait;

class StringType extends AbstractType implements \Stringable
{
    use PHPStringFunctions;

    use StringTypeStringRepresentationTrait;

    protected static function get_php_type(): string
    {
        return 'string';
    }
    protected static function validate_type($value): bool
    {
        return  in_array(gettype($value), ['string', 'float', 'integer', 'double']);
    }

    protected string $encoding = 'UTF-8';

    /**
     * Convert the string to a plain string.
     *
     * @return string
     */
    protected function to_string(): string
    {
        return $this->value;
    }

    /**
     * Mutator Methods
     *
     * - These methods modify the content of the current StringType instance. They are used to perform various string manipulation operations.
     * - They include methods to prepend a string, append a string,
     *       remove a specific string from the content, add or remove lines, repeat the string, trim whitespace, create a text box around the string, and apply padding.
     * - These methods are used for changing the content of the string and are crucial for string processing and formatting.
     */

    /**
     * Prepend a string to the beginning of the current string.
     *
     * @param string $string The string to prepend.
     *
     * @return static
     */
    public function prepend(string $string): static
    {
        $this->value = $string . $this->value;
        return $this;
    }

    /**
     * Append a string to the end of the current string.
     *
     * @param string $string The string to append.
     *
     * @return static
     */
    public function append(string $string): static
    {
        $this->value .= $string;
        return $this;
    }

    /**
     * Remove a specific string from the current string.
     *
     * @param string|array $substring The value getting removed. An array may to used to remove multiple substrings.
     *
     * @return static
     */
    public function remove_substring(string|array $substring, bool $case_sensetive = true, int &$count = 0): static
    {
        return $this->replace_substring($substring, '', $case_sensetive, $count);
    }

    /**
     * Remove a string from the start of the current string.
     *
     * @param string $substring The string to remove from the start.
     *
     * @return static
     */
    public function remove_beginning_substring(string $substring): static
    {
        if ($this->has_beginning($substring))
        {
            $this->get_substring(
                offset: StringType::len($substring)
            );
        }
        return $this;
    }

    final public static function make(self|string &$string): static
    {
        if (is_string($string))
        {
            $string = new self($string);
        }
        return $string;
    }

    /**
     * Surround the current string with another string.
     *
     * @param string|StringType $string The string to surround the current string with.
     *
     * @return static
     */
    public function surround(string|StringType $string): static
    {
        $this->value = $string . $this->value . StringType::rev($string);
        return $this;
    }
}
