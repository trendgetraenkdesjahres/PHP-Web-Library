<?php

namespace PHP_Library\Types\BaseTypeTraits;

trait PHPStringFunctions
{
    /**
     * Alias of strtr
     *
     * Translate characters or replace substrings
     *
     * @param string $from The string replacing from.
     * @param string $to The string being translated to to.
     * @return static
     */
    final public function translate(string $from, string $to): static
    {
        $this->value = strtr($this->value, $from, $to);
        return $this;
    }

    /**
     * Static alias of strtr
     *
     * Translate characters or replace substrings
     *
     * @param string $string The string being translated.
     * @param string $from The string replacing from.
     * @param string $to The string being translated to to.
     * @return static
     */
    final static public function tr(string $string, string $from, string $to): static
    {
        return new self(strtr($string, $from, $to));
    }

    /**
     * Alias of strlen
     *
     * Get string length
     *
     * @return int The length of the string on success, and 0 if the string is empty.
     */
    final public function get_length(): int
    {
        return strlen($this->value);
    }

    /**
     * Static alias of strlen
     *
     * Get string length
     *
     * @param string $string The string being measured for length
     * @return int The length of the string on success, and 0 if the string is empty.
     */
    final static public function len(string $string): int
    {
        return strlen($string);
    }

    /**
     * Alias of strpos and stripos
     *
     * Find the position of the first occurrence of a substring in this string
     *
     * @param string $needle If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
     * @param int $offset If specified, search will start this number of characters counted from the beginning of the string.
     * @param bool $case_sensetive
     * @return int<0,max>|false
     * Returns the position where the needle exists relative to the beginning of this string (independent of search direction or offset).
     * Also note that string positions start at 0, and not 1.
     * Returns FALSE if the needle was not found.
     */
    final public function get_first_postion(mixed $needle, int $offset = 0, bool $case_sensitive = true): int|false
    {
        if ($case_sensitive) {
            return strpos($this->value, $needle, $offset);
        }
        return stripos($this->value, $needle, $offset);
    }

    /**
     * Static alias of strpos and stripos
     *
     * Find the position of the first occurrence of a substring in this string
     *
     * @param string $haystack The string to search in
     * @param string $needle If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
     * @param int $offset If specified, search will start this number of characters counted from the beginning of the string.
     * @param bool $case_sensetive
     * @return int<0,max>|false
     * Returns the position where the needle exists relative to the beginning of this string (independent of search direction or offset).
     * Also note that string positions start at 0, and not 1.
     * Returns FALSE if the needle was not found.
     */
    final static function pos(string $haystack, mixed $needle, $offset = 0, bool $case_sensitive = true): int|false
    {
        if ($case_sensitive) {
            return strpos($haystack, $needle, $offset);
        }
        return stripos($haystack, $needle, $offset);
    }

    /**
     * Alias of strrpos and strripos
     *
     * Find the position of the last occurrence of a substring in this string
     *
     * @param string $needle If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
     * @param int $offset If specified, search will start this number of characters counted from the beginning of the string.
     * If the value is negative, search will instead start from that many characters from the end of the string, searching backwards.
     * @param bool $case_sensetive
     * @return int<0,max>|false
     * Returns the position where the needle exists relative to the beginning of this string (independent of search direction or offset).
     * Also note that string positions start at 0, and not 1.
     * Returns FALSE if the needle was not found.
     */
    final public function get_last_postion(mixed $needle, int $offset = 0, bool $case_sensitive = true): int|false
    {
        if ($case_sensitive) {
            return strrpos($this->value, $needle, $offset);
        }
        return strripos($this->value, $needle, $offset);
    }

    /**
     * Static alias of strrpos and strripos
     *
     * Find the position of the last occurrence of a substring in this string
     *
     * @param string $haystack The string to search in
     * @param string $needle If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
     * @param int $offset If specified, search will start this number of characters counted from the beginning of the string.
     * If the value is negative, search will instead start from that many characters from the end of the string, searching backwards.
     * @param bool $case_sensetive
     * @return int<0,max>|false
     * Returns the position where the needle exists relative to the beginning of this string (independent of search direction or offset).
     * Also note that string positions start at 0, and not 1.
     * Returns FALSE if the needle was not found.
     */
    final static public function rpos(string $haystack, mixed $needle, int $offset = 0, bool $case_sensitive = true): int|false
    {
        if ($case_sensitive) {
            return strrpos($haystack, $needle, $offset);
        }
        return strripos($haystack, $needle, $offset);
    }

    /**
     * Alias of strrev
     *
     * Reverse a string
     *
     * @return static the reversed string.
     */
    final public function reverse(): static
    {
        $this->value = strrev($this->value);
        return $this;
    }

    /**
     * Static alias of strrev
     *
     * Reverse a string
     * @param string $string The string to be reversed.
     * @return static the reversed string.
     */
    final static public function rev(string $string): static
    {
        return new self(strrev($string));
    }

    /**
     * Alias of str_pad
     *
     * Pad a string to a certain length by adding another string repeating on the right.
     *
     * @param int<0,max> $str_length If the value of $str_length is negative, less than, or equal to the length of this string, no padding takes place.
     * @param string $pad_string The pad_string may be truncated if the required number of padding characters can't be evenly divided by the pad_string's length.
     * @return static The string with the length of $str_length.
     */
    final public function pad_right(int $str_length, string $pad_string = " "): static
    {
        $this->value = str_pad($this->value, $str_length, $pad_string, STR_PAD_RIGHT);
        return $this;
    }

    /**
     * Alias of str_pad
     *
     * Pad a string to a certain length by adding another string repeating on the left.
     *
     * @param int<0,max> $str_length If the value of $str_length is negative, less than, or equal to the length of this string, no padding takes place.
     * @param string $pad_string The pad_string may be truncated if the required number of padding characters can't be evenly divided by the pad_string's length.
     * @return static The string with the length of $str_length.
     */
    final public function pad_left(int $str_length, string $pad_string = " "): static
    {
        $this->value = str_pad($this->value, $str_length, $pad_string, STR_PAD_LEFT);
        return $this;
    }

    /**
     * Alias of str_pad
     *
     * Pad a string to a certain length by adding another string repeating on both sides.
     *
     * @param int<0,max> $str_length If the value of $str_length is negative, less than, or equal to the length of this string, no padding takes place.
     * @param string $pad_string The pad_string may be truncated if the required number of padding characters can't be evenly divided by the pad_string's length.
     * @return static The string with the length of $str_length.
     */
    final public function pad_both(int $str_length, string $pad_string = " "): static
    {
        $this->value = str_pad($this->value, $str_length, $pad_string, STR_PAD_BOTH);
        return $this;
    }

    /**
     * Static alias of str_pad
     *
     * Pad a string to a certain length by adding another string repeating on the right.
     *
     * @param string $string The input string.
     * @param int<0,max> $str_length If the value of $str_length is negative, less than, or equal to the length of this string, no padding takes place.
     * @param string $pad_string The pad_string may be truncated if the required number of padding characters can't be evenly divided by the pad_string's length.
     * @param int $pad_type Cn be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH.
     * @return static The string with the length of $str_length.
     */
    final static public function pad(string $string, int $str_length, string $pad_string = " ", int $pad_type = STR_PAD_RIGHT): static
    {
        return new self(str_pad($string, $str_length, $pad_string, $pad_type));
    }

    /**
     * Alias of str_repeat
     *
     * Repeat a string
     *
     * @param int<0,max> $times Number of time the input string should be repeated. If the multiplier is set to 0, the function will return an empty string.
     * @return static — the repeated string.
     */
    final public function repeat($times): static
    {
        $this->value = str_repeat($this->value, $times);
        return $this;
    }

    /**
     * Alias of str_replace
     *
     * Replace all occurrences of the search string with the replacement string
     * @param string|string[] $substring The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
     * @param string|string[] $replacement The replacement value that replaces found search values. An array may be used to designate multiple replacements.
     * @param bool $case_sensetive
     * @param int &$count If passed, this will be set to the number of replacements performed.
     * @return static This string  with the replaced values.
     */
    final public function replace_substring(array|string $substring, array|string $replacement, bool $case_sensetive = true, int &$count = 0): static
    {
        if ($case_sensetive) {
            $this->value = str_replace($substring, $replacement, $this->value, $count);
        } else {
            $this->value = str_ireplace($substring, $replacement, $this->value, $count);
        }
        return $this;
    }

    /**
     * Static alias of str_replace
     *
     * Replace all occurrences of the search string with the replacement string
     * @param string $haystack The string being searched and replaced on.
     * @param string|string[] $needle The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
     * @param string|string[] $replacement The replacement value that replaces found search values. An array may be used to designate multiple replacements.
     * @param bool $case_sensetive
     * @param int &$count If passed, this will be set to the number of replacements performed.
     * @return static A string  with the replaced values.
     */
    final public static function replace(string $haystack, array|string $needle, array|string $replacement, bool $case_sensetive = true,  &$count = 0): static
    {
        if ($case_sensetive) {
            return new self(str_replace($needle, $replacement, $haystack, $count));
        }
        return new self(str_ireplace($needle, $replacement, $haystack, $count));
    }

    /**
     * Alias of str_shuffle
     *
     * Randomly shuffles a string
     *
     * @return static — the shuffled string.
     */
    final public function shuffle(): static
    {
        $this->value = str_shuffle($this->value);
        return $this;
    }

    /**
     * Alias of str_contains
     *
     * Checks if $substring is found in this string and returns a boolean value whether or not the $substring was found.
     *
     * @param string $substring
     * @return bool
     */
    final public function has_substring(string $substring): bool
    {
        return str_contains($this->value, $substring);
    }

    /**
     * Static alias of str_contains
     *
     * Checks if $needle is found in $haystack and returns a boolean value (true/false) whether or not the $needle was found.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    final public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Alias of str_split
     *
     * Convert a string to an array
     *
     * @param int<1,max> $length Maximum length of the chunk.
     * @return string[]|false The returned array will be broken down into chunks with each being split_length in length, FALSE is returned if split_length is less than 1.
     */
    final public function get_split_array(int $length = 1): array|false
    {
        return str_split($this->value, $length);
    }

    /**
     * Static alias of str_split
     *
     * Convert a string to an array
     *
     * @param string $string The input string.
     * @param int<1,max> $length Maximum length of the chunk.
     * @return string[]|false The returned array will be broken down into chunks with each being split_length in length, FALSE is returned if split_length is less than 1.
     */
    final static public function split(string $string, int $length = 1): array|false
    {
        return str_split($string, $length);
    }

    /**
     * Alias of explode
     *
     * Split a string by a string
     *
     * @param string $separator The boundary string, defaults to 'whitespace'
     * @param int $limit If limit is set and positive, the returned array will contain a maximum of limit elements with the last element containing the rest of string.
     * If the limit parameter is negative, all components except the last -limit are returned.
     * If the limit parameter is zero, then this is treated as 1.
     * @return string[]|false If delimiter is an empty string (""), explode will return false.
     * If delimiter contains a value that is not contained in string and a negative limit is used, then an empty array will be returned.
     * For any other limit, an array containing string will be returned.
     */
    final public function get_explode_array(string $separator = ' ', int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $this->value, $limit);
    }

    /**
     * Static alias of explode
     *
     * Split a string by a string
     *
     * @param string $separator The boundary string.
     * @param string $string The input string.
     * @param int $limit If limit is set and positive, the returned array will contain a maximum of limit elements with the last element containing the rest of string.
     * If the limit parameter is negative, all components except the last -limit are returned.
     * If the limit parameter is zero, then this is treated as 1.
     * @return string[]|false If delimiter is an empty string (""), explode will return false.
     * If delimiter contains a value that is not contained in string and a negative limit is used, then an empty array will be returned.
     * For any other limit, an array containing string will be returned.
     */
    final static public function explode(string $separator, string $string, int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $string, $limit);
    }

    /**
     * Alias of str_getcsv
     *
     * Parse this CSV string into an array
     *
     * @param string $separator Set the field delimiter (one character only).
     * @param string $enclosure Set the field enclosure character (one character only).
     * @param string $escape Set the escape character (one character only). Defaults as a backslash (\).
     * @return array an indexed array containing the fields read.
     */
    final public function get_csv_array(string $delimiter = ",", string $enclosure = '"', $escape = "\\"): array
    {
        return str_getcsv($this->value, $delimiter, $enclosure, $escape);
    }

    /**
     * Static alias of str_getcsv
     *
     * Parse a CSV string into an array
     *
     * @param string $string The string to parse.
     * @param string $separator Set the field delimiter (one character only).
     * @param string $enclosure Set the field enclosure character (one character only).
     * @param string $escape Set the escape character (one character only). Defaults as a backslash (\).
     * @return array an indexed array containing the fields read.
     */
    final static public function getcsv(string $string, string $delimiter = ",", string $enclosure = '"', $escape = "\\"): array
    {
        return str_getcsv($string, $delimiter, $enclosure, $escape);
    }

    /**
     * Alias of json_decode
     *
     * Parse this JSON string into an array
     * @param int $flags Bitmask consisting of
     * JSON_HEX_QUOT,JSON_HEX_TAG,JSON_HEX_AMP,JSON_HEX_APOS,JSON_NUMERIC_CHECK,JSON_PRETTY_PRINT,JSON_UNESCAPED_SLASHES,JSON_FORCE_OBJECT,JSON_UNESCAPED_UNICODE.JSON_THROW_ON_ERROR
     * The behaviour of these constants is described on the JSON constants page.
     * @param int<1,max> $depth Set the maximum depth. Must be greater than zero.
     * @return array a Jarray representation of JSON data on success or FALSE on failure.
     */
    final public function get_json_array(int $depth = 512, int $flags = 0): array|false
    {
        return json_decode($this->value, true, $depth, $flags);
    }

    /**
     * Alias of strtolower
     *
     * Make a string lowercase
     * @return static — the lowercased string.
     */
    final public function lower_case(): static
    {
        $this->value = strtolower($this->value);
        return $this;
    }

    /**
     * Static alias of strtolower
     *
     * Make a string lowercase
     * @param string $string The input string.
     * @return static The lowercased string.
     */
    final public function tolower(string $string): static
    {
        return new self(strtolower($string));
    }

    /**
     * Alias of strtoupper
     *
     * Make a string uppercase
     * @return static The uppercased string.
     */
    final public function upper_case(): static
    {
        $this->value = strtoupper($this->value);
        return $this;
    }

    /**
     * Static alias of strtoupper
     *
     * Make a string uppercase
     * @param string $string The input string.
     * @return static The uppercased string.
     */
    final public function toupper(string $string): static
    {
        return new self(strtoupper($string));
    }

    /**
     * Alias of str_starts_with
     *
     * The function returns true if this string starts from the $substring string or false otherwise.
     *
     * @param string $substring
     * @return bool
     */
    final public function has_beginning(string $substring): bool
    {
        return str_starts_with($this->value, $substring);
    }

    /**
     * Static alias of str_starts_with
     *
     * The function returns true if the passed $haystack starts from the $needle string or false otherwise.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    final public static function starts_with(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Alias of str_ends_with
     *
     * The function returns true if this string ends with the $substring string or false otherwise.
     *
     * @param string $substring
     * @return bool
     */
    final public function has_ending(string $substring): bool
    {
        return str_ends_with($this->value, $substring);
    }

    /**
     * Static alias of str_ends_with
     *
     * The function returns true if the passed $haystack ends with the $needle string or false otherwise.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    final public static function ends_with(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Alias of substr
     *
     * Return part of a string or empty string on failure.
     * @param int $offset If start is positve, the returned string will start at the start'th position in string, counting from zero.
     * If start is negative, the returned string will start at the start'th character from the end of string.
     * @param int $length If length is positive, the string returned will contain at most length characters beginning from start (depending on the length of string).
     * If length is given and is negative, then that many characters will be omitted from the end of string (after the start position has been calculated when a start is negative).
     * If start denotes a position beyond this truncation, an empty string will be returned.
     * If length is given and is 0, false or null an empty string will be returned.
     * @return static The substring
     */

    final public function get_substring(int $offset, ?int $length = null): static
    {
        return new self(substr($this->value, $offset, $length));
    }

    /**
     * Alias for trim
     *
     * Strip whitespace (or other characters) from the beginning and end of a string
     *
     * @param string $characters Characters to trim. Default is whitespace characters.
     *
     * @return static
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): static
    {
        $this->value = trim($this->value, $characters);
        return $this;
    }

    /**
     * Alias for wordwrap
     *
     * Wraps a string to a given number of characters
     * @param int $max_length The number of characters at which the string will be wrapped.
     * @param bool $cut_long_words If the cut is set to true, the string is always wrapped at or before the specified width. So if you have a word that is larger than the given width, it is broken apart. (See second example).
     * @return static
     */
    public function word_wrap(int $max_length, bool $cut_long_words = true): static
    {
        $this->value = wordwrap(
            string: $this->value,
            width: $max_length,
            break: PHP_EOL,
            cut_long_words: $cut_long_words
        );
        return $this;
    }

    final public static function implode(array $array, string $separator = ""): static
    {
        return new self(implode($separator, $array));
    }
    // str_word_count() get_word_count
    // strtotime() get_timestamp
}
