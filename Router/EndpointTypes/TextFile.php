<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Router\Endpoint;
use PHP_Library\Superglobals\Server;

class TextFile extends Endpoint
{
    private string $file;

    public array $http_headers = [
        'content-type' => ['text/html', 'charset' => 'utf-8']
    ];

    public function get_content(): string|false
    {
        return @file_get_contents($this->file);
    }

    protected function constructor(mixed $file): static
    {
        $file =  static::get_abs_path($file);
        $this->file = $file;
        return $this;
    }
}
