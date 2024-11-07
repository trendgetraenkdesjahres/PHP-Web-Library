<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Error\Error;
use PHP_Library\Router\Endpoint;

class PHPFile extends Endpoint
{
    private string $file;

    public array $http_headers = [
        'content-type' => ['text/html', 'charset' => 'utf-8']
    ];

    public function get_content(): string
    {
        ob_start();
        require $this->file;
        return ob_get_clean();
    }

    protected function constructor(mixed $file): static
    {
        if (!is_file($file)) {
            throw new Error("\$file is not a file");
        }
        $this->file = $file;
        return $this;
    }
}
