<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Error\Error;
use PHP_Library\Router\Endpoint;

class Attachment extends Endpoint
{
    private string $file;

    public function get_content(): string
    {
        ???
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
