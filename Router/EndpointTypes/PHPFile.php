<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Error\Error;
use PHP_Library\Router\Endpoint;

class PHPFile extends Endpoint
{
    private string $file;

    private array $vars = [];

    public array $http_headers = [
        'content-type' => ['text/html', 'charset' => 'utf-8']
    ];

    public function add_variable(string $name, mixed $value): static
    {
        $this->vars[$name] = $value;
        return $this;
    }

    public function get_content(): string
    {
        ob_start();
        foreach ($this->vars as $var_name => $value) {
            $$var_name = $value;
        }
        require $this->file;
        return ob_get_clean();
    }

    protected function constructor(mixed $file): static
    {
        $file =  static::get_abs_path($file);
        if (!is_file($file)) {
            throw new Error("\$file is not a file");
        }
        $this->file = $file;
        return $this;
    }
}
