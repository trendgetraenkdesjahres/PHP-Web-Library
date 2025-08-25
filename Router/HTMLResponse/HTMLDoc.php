<?php

namespace PHP_Library\Router\HTMLResponse;

use PHP_Library\Error\Error;
use PHP_Library\Router\Router;
use PHP_Library\Superglobals\Get;

class HTMLDoc
{
    static protected array $template_files = [];

    static protected string $default_template_file = __DIR__ . '/default_html_template.php';

    protected static array $overwrite_content = [
        'head' => [],
        'body' => []
    ];

    static public function get_rendered(string $content, mixed ...$variable): string
    {
        $variable['content'] = $content;
        extract($variable);
        ob_start();
        $template_file = static::get_matching_templatefile();
        include $template_file;
        $html_doc = ob_get_clean();
        if ($endpoint_title = Router::$current_endpoint->get_title())
        {
            static::$overwrite_content['head']['title'] = $endpoint_title;
        }
        $html_doc = static::overwrite_singular_tag($html_doc, 'title');
        $html_doc = static::append_repetitive_tag($html_doc, 'meta');
        $html_doc = static::append_repetitive_tag($html_doc, 'style');
        $html_doc = static::append_repetitive_tag($html_doc, 'script');
        $html_doc = static::overwrite_singular_tag($html_doc, 'base');
        $html_doc = static::append_repetitive_tag($html_doc, 'link');
        return $html_doc;
    }

    /**
     * set template file. to populate it with content, use the `$content` variable inside the file.
     *
     * @param string $path
     * @return void
     */
    public static function set_template_file(string $path, string $preg_pattern = '/.*/'): void
    {
        if (!str_starts_with($path, '/'))
        {
            $path = $_SERVER['DOCUMENT_ROOT'] . "/{$path}";
        }

        array_unshift(static::$template_files, [
            'preg_pattern' => $preg_pattern,
            'file' => $path
        ]);
    }

    // only one title elem per html doc
    public static function set_title(string $title): void
    {
        static::$overwrite_content['head']['title'] = $title;
    }

    public static function add_style(string $css): void
    {
        if (!isset(static::$overwrite_content['head']['style']))
        {
            static::$overwrite_content['head']['style'] = [];
        }
        array_push(static::$overwrite_content['head']['style'], $css);
    }

    public static function add_script(string $js): void
    {
        if (!isset(static::$overwrite_content['head']['script']))
        {
            static::$overwrite_content['head']['script'] = [];
        }
        array_push(static::$overwrite_content['head']['script'], $js);
    }

    // only one base elem per html doc
    public static function set_base(array $attributes): void
    {
        static::$overwrite_content['head']['base'] = $attributes;
    }

    public static function add_meta(array $attributes): void
    {
        if (!isset(static::$overwrite_content['head']['meta']))
        {
            static::$overwrite_content['head']['meta'] = [];
        }
        array_push(static::$overwrite_content['head']['meta'], $attributes);
    }

    public static function add_link(array $attributes): void
    {
        if (!isset(static::$overwrite_content['head']['link']))
        {
            static::$overwrite_content['head']['link'] = [];
        }
        array_push(static::$overwrite_content['head']['link'], $attributes);
    }

    protected static function append_repetitive_tag(string $html_doc, string $tag_name): string
    {
        if (! isset(static::$overwrite_content['head'][$tag_name]))
        {
            return $html_doc;
        }
        $new_tags = '';
        foreach (static::$overwrite_content['head'][$tag_name] as $key => $content)
        {
            if (is_array($content))
            {
                $new_tags .= static::create_singular_tag($tag_name, $content) . PHP_EOL;
            }
            else
            {
                $new_tags .= "<{$tag_name}>{$content}</{$tag_name}>" . PHP_EOL;
            }
        }
        $current_tags = [];
        if (
            preg_match_all('/<' . $tag_name . '.*?<\/' . $tag_name . '>/', $html_doc, $current_tags)
            || preg_match_all('/<' . $tag_name . '.*?>/', $html_doc, $current_tags)
        )
        {
            $last_tag = end($current_tags[0]);
            return str_replace(
                $last_tag,
                $last_tag . PHP_EOL . $new_tags,
                $html_doc
            );
        }
        return str_replace(
            "</head>",
            $new_tags . "</head>",
            $html_doc
        );
    }

    protected static function overwrite_singular_tag(string $html_doc, string $tag_name): string
    {
        if (! isset(static::$overwrite_content['head'][$tag_name]))
        {
            return $html_doc;
        }
        return preg_replace(
            '/<' . $tag_name . '>(.*?)<\/' . $tag_name . '>/i',
            static::create_singular_tag($tag_name),
            $html_doc
        );
    }

    protected static function create_singular_tag(string $tag_name, ?array $attributes = null): string
    {
        // new attributes
        $opening_tag = "<{$tag_name}";
        if (! is_null($attributes))
        {
            foreach ($attributes as $key => $value)
            {
                $opening_tag .= " {$key}=\"{$value}\"";
            }
        }
        else
        {
            if (is_array(static::$overwrite_content['head'][$tag_name]))
            {
                foreach (static::$overwrite_content['head'][$tag_name] as $key => $value)
                {
                    $opening_tag .= " {$key}=\"{$value}\"";
                }
            }
        }

        $tag = $opening_tag . '>';
        // new content
        if (is_string(static::$overwrite_content['head'][$tag_name]))
        {
            $tag .= htmlspecialchars(static::$overwrite_content['head'][$tag_name]) . "</{$tag_name}>";
        }
        return $tag;
    }

    public static function get_matching_templatefile(): string
    {
        if (empty(static::$template_files))
        {
            return static::$default_template_file;
        }
        $path = Get::get_path();
        foreach (static::$template_files as $template_file)
        {
            if (false === preg_match($template_file['preg_pattern'], $path, $matches))
            {
                throw new Error("Invalid preg pattern: '{$template_file['preg_pattern']}'");
            }
            if ($matches)
            {
                return $template_file['file'];
            }
        }
        return static::$default_template_file;
    }
}
