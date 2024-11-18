<?php

namespace  PHP_Library\Router;

use PHP_Library\Element\Element;
use PHP_Library\Router\EndpointTypes\Callback;
use PHP_Library\Router\EndpointTypes\PHPFile;
use PHP_Library\Router\EndpointTypes\Redirect;
use PHP_Library\Router\EndpointTypes\TextFile;
use Stringable;

abstract class Endpoint implements Stringable
{
    public string $path;

    public string $http_method;

    public array $http_headers = [];

    public int $status_code = 200;

    protected string $title;

    abstract protected function constructor(mixed $content): static;

    abstract public function get_content(): string|false;

    static function new_callback_endpoint(string $path, callable $function, string $http_method = 'get'): Callback
    {
        return new Callback($path, $function, $http_method);
    }

    static function new_php_file_endpoint(string $path, string $file, string $http_method = 'get'): PHPFile
    {
        return new PHPFile($path, $file, $http_method);
    }

    static function new_redirect_endpoint(string $path, string|Endpoint $location, int $code = 301, string $http_method = 'get'): Redirect
    {
        $endpoint = new Redirect($path, $location, $http_method);
        $endpoint->status_code = $code;
        return $endpoint;
    }

    static function new_text_file_endpoint(string $path, string $file, string $http_method = 'get'): TextFile
    {
        return new TextFile($path, $file, $http_method);
    }

    public function __construct(string $path, mixed $content, $http_method = 'get')
    {
        $this->path = str_starts_with($path, '/') ? $path : "/$path";
        $this->http_method = $http_method;
        $this->constructor($content);
        Router::add_endpoint($this);
    }

    public function add_http_header($field, $value): static
    {
        $this->http_headers[$field] = $value;
        return $this;
    }

    public function get_link(?string $text = null): Element
    {
        if ($text) {
            return new Element('a', ['href' => $this->path], $text);
        }
        if (isset($this->title)) {
            return new Element('a', ['href' => $this->path], $this->title);
        }
        return new Element('a', ['href' => $this->path], '@');
    }

    public function set_title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function get_title(): string
    {
        return $this->title ?? '';
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
