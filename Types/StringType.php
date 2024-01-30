<?php

namespace Library\Types;

/**
 * StringType represents a string manipulation class designed to extend and enhance PHP's string handling capabilities.
 * It provides various methods for string processing, including concatenation, removal, padding, and ANSI formatting.
 * StringType is particularly useful when working with string data and performing common text operations.
 *
 * Usage:
 * - Use StringType when you need advanced string manipulation operations beyond PHP's built-in functions.
 * - Benefit from its methods for modifying, formatting, and enhancing strings to suit your application's needs.
 *
 * @package TypeWrap
 */
class StringType extends TypeWrap
{
    private string|null $encoding = null;
    /**
     * Interface Methods
     *
     * - These methods provide the fundamental functionality and behavior for instances of the StringType class.
     * - They include methods like the constructor, converting the string to an array,
     *      getting lines, checking if the string starts with a specific substring, and getting the length of the string.
     * - These methods are essential for basic operations on string objects.
     *
     */

    /**
     * StringType constructor.
     *
     * @param string $value [optional] The initial string value. Default is an empty string.
     */
    public function __construct(mixed $value = '')
    {
        $this->value = $value;
    }


    /**
     * Convert the string to a plain string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
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
     * Check if the string contains a break line
     *
     * @return bool
     */
    public function is_empty(): bool
    {
        return empty($this->value);
    }

    /**
     * Check if the string starts with a certain string.
     *
     * @param string|StringType $needle The string to check for at the beginning.
     *
     * @return bool
     */
    public function is_starting_with(string|StringType $needle): bool
    {
        if (!is_string($needle)) {
            $needle = (string) $needle;
        }
        return (strripos($this->value, (string) $needle) === 0);
    }

    /**
     * Get the length of the string.
     *
     * This method removes ANSI Control Characters before counting the length by default.
     * @param int|null $count_ansi_chars [optional] If true, it will also count ANSI chars.
     *
     * @return int
     */
    public function get_length(?bool $count_ansi_chars = null): int
    {
        if (!$count_ansi_chars) {
            $string = str_replace(
                [
                    "\x00",
                    "\e[1m",
                    "\e[3m",
                    "\e[4m",
                    "\e[9m",
                    "\e(B\e[m"
                ],
                '',
                $this->value
            );
            $string_length = mb_strlen($string, $this->encoding ? $this->encoding : 'UTF-8');
            return $string_length;
        }
        $string_length = mb_strlen($this->value, $this->encoding ? $this->encoding : 'UTF-8');
        return $string_length;
    }

    /**
     * Convert the string to an array of (string) elements by splitting it using a separator.
     *
     * @param string $separator The boundary string.
     * @param int|null $limit [optional] If set and positive, limits the number of elements in the array.
     *
     * @return ArrayType
     */
    public function to_array($seperator = PHP_EOL, ?int $limit = null): ArrayType
    {
        return new ArrayType(explode(
            separator: $seperator,
            string: $this->value,
            limit: $limit ? -1 : $limit
        ));
    }

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
            $array_of_strings[$line] = new StringType($string);
        }

        return new ArrayType($array_of_strings);
    }

    /**
     * Get the longest line from the current string.
     *
     * @return StringType
     */
    public function get_longest_line(): StringType
    {
        $longest_line_length = 0;
        $longest_line = new StringType();
        foreach ($this->get_lines() as $line) {
            if ($line->get_length() > $longest_line_length) {
                $longest_line_length = $line->get_length();
                $longest_line = $line;
            }
        }
        try {
            return $longest_line;
        } catch (\Throwable $th) {
            new Warning($th->getMessage());
        }
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
     * @param string|StringType $string The string to prepend.
     *
     * @return StringType
     */
    public function prepend(string|StringType $string): StringType
    {
        $this->value = $string . $this->value;
        return $this;
    }

    /**
     * Append a string to the end of the current string.
     *
     * @param string|StringType $string The string to append.
     *
     * @return StringType
     */
    public function append(string|StringType $string): StringType
    {
        $this->value .= $string;
        return $this;
    }

    /**
     * Replaces a specific string with another string from the current string.
     *
     * @param string|StringType $search The string to search.
     * @param string|StringType $replace The string to replace.
     *
     * @return StringType
     */
    public function replace(string|StringType $search, string|StringType $replace): StringType
    {
        $this->value = str_replace((string) $search, (string) $replace, $this->value);
        return $this;
    }

    /**
     * Remove a specific string from the current string.
     *
     * @param string|StringType $string The string to remove.
     *
     * @return StringType
     */
    public function remove_string(string|StringType $string): StringType
    {
        $this->value = str_replace((string) $string, '', $this->value);
        return $this;
    }

    /**
     * Remove a string from the start of the current string.
     *
     * @param string|StringType $string The string to remove from the start.
     *
     * @return StringType
     */
    public function remove_string_at_start(string|StringType $string): StringType
    {
        $string = is_string($string) ? new StringType($string) : $string;
        if ($this->is_starting_with($string)) {
            $this->value = substr($this->value, $string->get_length());
        }
        return $this;
    }

    /**
     * Append a string as a new line to the current string.
     *
     * @param string|StringType $string The string to append as a new line.
     *
     * @return StringType
     */
    public function append_line(string|StringType $string): StringType
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
     * Repeat the current string a specified number of times.
     *
     * @param int $times The number of times to repeat the string.
     *
     * @return StringType
     */
    public function repeat(int $times): StringType
    {
        $this->value = str_repeat($this->value, $times);
        return $this;
    }

    /**
     * Trim the current string by removing specified characters from the beginning and end.
     *
     * @param string $characters [optional] Characters to trim. Default is whitespace characters.
     *
     * @return StringType
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): StringType
    {
        $this->value = trim($this->value);
        return $this;
    }

    /**
     * Surround the current string with another string.
     *
     * @param string $string The string to surround the current string with.
     *
     * @return StringType
     */
    public function surround(string $string): StringType
    {
        $this->value = $string . $this->value . strrev($string);
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
    public function padding_left(int $padding = 1, int $top_offset = 0): StringType
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
     * @return StringType
     */
    public function padding_right(int $padding = 1, int $top_offset = 0): StringType
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
                Warning::trigger("Can't add $padding padding to '$line_content'");
            }
        }
        $this->value = trim($padded_string, PHP_EOL);
        return $this;
    }

    public function word_wrap(int $max_length, bool $cut_long_words = true): StringType
    {
        $this->value = wordwrap(
            string: $this->value,
            width: $max_length,
            break: PHP_EOL,
            cut_long_words: $cut_long_words
        );
        return $this;
    }

    /**
     * Sanitize the current string by removing null characters.
     *
     * @param string $encoding [optional] The character encoding. Default is 'UTF-8'.
     *
     * @return StringType
     */
    public function sanitize(string $encoding = 'UTF-8'): StringType
    {
        $this->value = str_replace("\x00", '', $this->value);
        return $this;
    }

    /**
     * Create a text box around the current string with optional title and padding.
     *
     * @param int $padding [optional] The amount of padding around the string content. Default is 4.
     * @param null|string|StringType $title [optional] The title for the box. Default is null.
     *
     * @return StringType
     */
    public function box_around_string(int $padding = 4, null|string|StringType $title = null): StringType
    {
        $top_left = "\xe2\x95\xad";
        $top_right = "\xe2\x95\xae";
        $btm_right = "\xe2\x95\xaf";
        $btm_left = "\xe2\x95\xb0";
        $horizontal = "\xe2\x94\x80";
        $vertical = "\xe2\x94\x82";

        $longest_line_length  = $this->get_longest_line()->get_length();
        if ($title) {
            $title = is_string($title) ? new StringType($title) : $title;
        }


        $box_string = new StringType();
        foreach ($this->get_lines() as $i => $line) {
            if (!$line->is_empty()) {
                $line->prepend($vertical . str_repeat(' ', $padding));
                $line->padding_right((1 + $padding + $longest_line_length) - $line->get_length())
                    ->append(str_repeat(' ', $padding) . $vertical);
                $box_string->append_line($line);
            }
        }

        if (!$title) {
            $top_border =  $top_left . str_repeat(
                string: $horizontal,
                times: $longest_line_length  + ($padding * 2)
            ) . $top_right;
        } else {
            if (($longest_line_length  + ($padding * 2) - $title->get_length()) < 0) {
                $top_border =  $title . PHP_EOL;
            } else {
                $top_border =  $title . str_repeat(
                    string: $horizontal,
                    times: $longest_line_length  +  1 + ($padding * 2) - $title->get_length()
                ) . $top_right;
            }
        }

        $bottom_border =  $btm_left . str_repeat(
            string: $horizontal,
            times: $longest_line_length  + ($padding * 2)
        ) . $btm_right;

        $this->value = $top_border . PHP_EOL . $box_string . PHP_EOL . $bottom_border . PHP_EOL;
        return $this;
    }


    /**
     * ANSI Formatting Methods
     *
     * - These methods apply or remove ANSI escape sequences to the string, allowing for text formatting in terminals that support ANSI formatting.
     * - Methods like ansi_format_bold, ansi_format_italic, ansi_format_underline, ansi_format_strikethroug, and ansi_format_remove apply or remove text formatting styles.
     * - ANSI formatting is commonly used to emphasize or style text in command-line interfaces.
     */

    /**
     * Apply bold ANSI formatting to the current string.
     *
     * @return StringType
     */
    public function ansi_format_bold(): StringType
    {

        $this->value = "\e[1m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Apply italic ANSI formatting to the current string.
     *
     * @return StringType
     */
    public function ansi_format_italic(): StringType
    {
        $this->value = "\e[3m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Apply underline ANSI formatting to the current string.
     *
     * @return StringType
     */
    public function ansi_format_underline(): StringType
    {
        $this->value = "\e[4m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Apply strikethrough ANSI formatting to the current string.
     *
     * @return StringType
     */
    public function ansi_format_strikethroug(): StringType
    {
        $this->value = "\e[9m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Remove ANSI formatting from the current string.
     *
     * @return StringType
     */
    public function ansi_format_remove(): StringType
    {
        $this->value = str_replace(
            [
                "\x00",
                "\e[1m",
                "\e[3m",
                "\e[4m",
                "\e[9m",
                "\e(B\e[m"
            ],
            '',
            $this->value
        );
        return $this;
    }
}
