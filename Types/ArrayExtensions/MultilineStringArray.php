<?php

namespace PHP_Library\Types\ArrayExtension;

use PHP_Library\Types\ArrayType;
use PHP_Library\Types\StringType;

class MultilineStringArray extends ArrayType
{

    public function __construct(string|StringType $multiline_string)
    {
        $array_of_strings = ArrayType::explode($multiline_string, PHP_EOL);
        $this->value = $array_of_strings->walk(function ($value)
        {
            $value = new StringType($value);
        })->get_value();
    }

    /**
     * Get the longest line from the current string.
     *
     * @return StringType
     */
    public function get_longest_line(): StringType
    {
        return ArrayType::usort($this->value, function (StringType $a, StringType $b)
        {
            return $a->get_length() <=> $b->get_length();
        })->get_first_element();
    }

    /**
     * Append a string as a new line to the current string.
     *
     * @param string|StringType $string The string to append as a new line.
     *
     * @return StringType
     */
    public function append(string|StringType $string): static
    {
        $this->push(StringType::make($string));
        return $this;
    }

    /**
     * Add left padding to each line of the current string.
     *
     * @param int $padding The amount of left padding to add.
     * @param int $top_offset [optional] The number of lines to skip before adding padding.
     *
     * @return StringType
     */
    public function padding_left(int $padding = 1, int $top_offset = 0): static
    {
        return $this->walk(function (StringType $string, int $line) use ($padding, $top_offset)
        {
            if ($line > $top_offset) $string->pad_left($padding);
        });
    }

    /**
     * Add right padding to each line of the current string.
     *
     * @param int $padding The amount of right padding to add.
     * @param int $top_offset [optional] The number of lines to skip before adding padding.
     *
     * @return StringType
     */
    public function padding_right(int $padding = 1, int $top_offset = 0): static
    {
        return $this->walk(function (StringType $string, int $line) use ($padding, $top_offset)
        {
            if ($line > $top_offset) $string->pad_left($padding);
        });
    }
}
