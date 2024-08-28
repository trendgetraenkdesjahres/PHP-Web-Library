<?php

namespace PHP_Library\Types\StringExtension;

use PHP_Library\Types\Str;

class MultilineString extends Str
{
    /**
     * Convert the string to an array of (StringType) lines.
     *
     * @param int $limit [optional] Maximum number of lines to include in the array.
     *
     * @return ArrayType
     */
    public function get_lines(int $limit = PHP_INT_MAX): ArrayType
    {
        $array_of_strings = explode(
            separator: PHP_EOL,
            string: $this->value,
            limit: $limit
        );
        foreach ($array_of_strings as $line => $string) {
            $array_of_strings[$line] = new Str($string);
        }

        return new ArrayType($array_of_strings);
    }

    /**
     * Get the longest line from the current string.
     *
     * @return Str
     */
    public function get_longest_line(): Str
    {
        $longest_line_length = 0;
        $longest_line = new Str();
        foreach ($this->get_lines() as $line) {
            if ($line->get_length() > $longest_line_length) {
                $longest_line_length = $line->get_length();
                $longest_line = $line;
            }
        }
        return $longest_line;
    }

    /**
     * Check if the string contains a break line
     *
     * @return bool
     */
    public function has_linebreak(): bool
    {
        return is_int(strpos($this->value, PHP_EOL));
    }

    /**
     * Append a string as a new line to the current string.
     *
     * @param string|Str $string The string to append as a new line.
     *
     * @return Str
     */
    public function append_line(string|Str $string): Str
    {
        if (!is_string($string)) {
            $string = $string->value;
        }
        if (empty($this->value)) {
            $this->value = $string;
        } else if (!empty($string)) {
            $this->value .= PHP_EOL . $string;
        }
        return $this;
    }


    /**
     * Surround the current string with another string.
     *
     * @param string $string The string to surround the current string with.
     *
     * @return static
     */
    public function surround(string $string): static
    {
        $this->value = $string . $this->value . Str::rev($string);
        return $this;
    }

    /**
     * Add left padding to each line of the current string.
     *
     * @param int $padding The amount of left padding to add.
     * @param int $top_offset [optional] The number of lines to skip before adding padding.
     *
     * @return Str
     */
    public function padding_left(int $padding = 1, int $top_offset = 0): Str
    {
        $padded_string = '';
        foreach (explode(PHP_EOL, $this->value) as $line => $line_content) {
            if ($line < $top_offset) {
                $padded_string .= $line_content;
                continue;
            }
            $padded_string .= PHP_EOL . str_repeat(" ", $padding) . $line_content;
        }
        $this->value = $padded_string;
        return $this;
    }

    /**
     * Add right padding to each line of the current string.
     *
     * @param int $padding The amount of right padding to add.
     * @param int $top_offset [optional] The number of lines to skip before adding padding.
     *
     * @return Str
     */
    public function padding_right(int $padding = 1, int $top_offset = 0): Str
    {
        $padded_string = '';
        foreach (explode(PHP_EOL, $this->value) as $line => $line_content) {
            if ($line < $top_offset) {
                $padded_string .= $line_content;
                continue;
            }
            if ($padding >= 0) {
                $padded_string .= PHP_EOL . $line_content . str_repeat(" ", $padding);
            } else {
                PHP_Library\Warning::trigger("Can't add $padding padding to '$line_content'");
            }
        }
        $this->value = trim($padded_string, PHP_EOL);
        return $this;
    }
}
