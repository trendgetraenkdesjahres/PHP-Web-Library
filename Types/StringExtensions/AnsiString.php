<?php

namespace PHP_Library\Types\StringExtension;

use PHP_Library\Types\Str;

class AnsiString extends Str
{

    /**
     * ANSI Formatting Methods
     *
     * - These methods apply or remove ANSI escape sequences to the string, allowing for text formatting in terminals that support ANSI formatting.
     * - Methods like ansi_format_bold, ansi_format_italic, ansi_format_underline, ansi_format_strikethroug, and ansi_format_remove apply or remove text formatting styles.
     * - ANSI formatting is commonly used to emphasize or style text in command-line interfaces.
     */

    /**
     * Sanitize the current string by removing null characters
     *
     * @return Str
     */
    public function remove_null_characters(): Str
    {
        $this->value = str_replace("\x00", '', $this->value);
        return $this;
    }

    /**
     * Create a text box around the current string with optional title and padding.
     *
     * @param int $padding [optional] The amount of padding around the string content. Default is 4.
     * @param null|string|Str $title [optional] The title for the box. Default is null.
     *
     * @return Str
     */
    public function box_around_string(int $padding = 4, null|string|Str $title = null): Str
    {
        $top_left = "\xe2\x95\xad";
        $top_right = "\xe2\x95\xae";
        $btm_right = "\xe2\x95\xaf";
        $btm_left = "\xe2\x95\xb0";
        $horizontal = "\xe2\x94\x80";
        $vertical = "\xe2\x94\x82";

        $longest_line_length  = $this->get_longest_line()->get_length();
        if ($title) {
            $title = is_string($title) ? new Str($title) : $title;
        }


        $box_string = new Str();
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
     * Apply bold ANSI formatting to the current string.
     *
     * @return Str
     */
    public function format_bold(): Str
    {

        $this->value = "\e[1m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Apply italic ANSI formatting to the current string.
     *
     * @return Str
     */
    public function format_italic(): Str
    {
        $this->value = "\e[3m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Apply underline ANSI formatting to the current string.
     *
     * @return Str
     */
    public function format_underline(): Str
    {
        $this->value = "\e[4m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Apply strikethrough ANSI formatting to the current string.
     *
     * @return Str
     */
    public function format_strikethrough(): Str
    {
        $this->value = "\e[9m$this->value\e(B\e[m";
        return $this;
    }

    /**
     * Remove ANSI formatting from the current string.
     *
     * @return Str
     */
    public function format_remove(): Str
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

    /**
     * Get the length of the string.
     *
     * This method removes ANSI Control Characters before counting the length by default.
     *
     * @return int
     */
    public function get_clean_length(): int
    {
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
        return mb_strlen($string, $this->encoding);
        $string_length;
    }
}
