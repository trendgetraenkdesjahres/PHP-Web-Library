<?php

namespace PHP_Library\Router\HTMLResponse;

class HTMLDoc
{
    static protected string $template_file  = __DIR__ . '/default.php';

    static public function get_rendered(string $content, mixed ...$variable): string
    {
        $variable['content'] = $content;
        extract($variable);
        ob_start();
        include static::$template_file;
        $output = ob_get_clean();
        return $output;
    }

    public static function set_template_file(string $path)
    {
        static::$template_file = $path;
    }
}
