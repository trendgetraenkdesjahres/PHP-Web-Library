<?php

namespace PHP_Library\MarkdownParser;

class MarkdownParser
{
    static ?int $list_counter = null;
    static ?string $list_type = null;

    static public function to_html(string $markdown): string
    {
        $html = '';

        // things that start with a symbol at row-start
        foreach (explode(PHP_EOL, $markdown) as $row) {
            if (is_int(static::$list_counter)) {
                if (str_starts_with($row, static::$list_counter = static::$list_counter + 1 . ". ")) {
                    $html .= "<li>" . substr($row, 3) . "</li>" . PHP_EOL;
                    continue;
                } else {
                    $html .= PHP_EOL . "</" . static::$list_type . ">" . PHP_EOL;
                    static::$list_counter = null;
                    static::$list_type = null;
                }
            }
            if (str_starts_with($row, '# ')) {
                $html .= "<h1>" . substr($row, 2) . "</h1>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '## ')) {
                $html .= "<h2>" . substr($row, 3) . "</h2>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '### ')) {
                $html .= "<h3>" . substr($row, 4) . "</h3>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '#### ')) {
                $html .= "<h4>" . substr($row, 5) . "</h4>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '##### ')) {
                $html .= "<h5>" . substr($row, 6) . "</h5>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '###### ')) {
                $html .= "<h6>" . substr($row, 7) . "</h6>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '> ')) {
                $html .= "<blockquote><p>" . substr($row, 2) . "</p></blockquote>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '1. ')) {
                static::$list_counter = 1;
                static::$list_type = 'ol';
                $html .= "<ol>" . PHP_EOL . "<li>" . substr($row, 3) . "</li>" . PHP_EOL;
                continue;
            }
            if (str_starts_with($row, '- ')) {
                static::$list_counter = 1;
                static::$list_type = 'ul';
                $html .= "<ul>" . PHP_EOL . "<li>" . substr($row, 3) . "</li>" . PHP_EOL;
                continue;
            }
            if (!trim($row)) {
                continue;
            }
            $html .= "<p>{$row}.</p>";
        }

        // things that appear somewhere in a line
        $html = preg_replace('/(\*\*\*(.*)\*\*\*)/', '<b>$2</b>', $html);
        $html = preg_replace('/(\*(.*)\*)/', '<i>$2</i>', $html);
        $html = preg_replace('/(`(.*)`)/', '<code>$2</code>', $html);
        $html = preg_replace('/\[(.*)\]\((.*)\)/', '<a href="$2">$1</a>', $html);
        return $html;
    }
}
