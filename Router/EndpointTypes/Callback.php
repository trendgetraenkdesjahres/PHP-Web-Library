<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Error\Error;
use PHP_Library\Router\Endpoint;

class Callback extends Endpoint
{
    private array $callback = [];

    public function get_content(): string
    {
        $content = call_user_func($this->callback[0]);
        if (!is_string($content)) {
            throw new Error("\$callback must return string");
        }
        return $content;
    }

    protected function constructor(mixed $callback): static
    {
        if (!is_callable($callback)) {
            throw new Error("\$callback is not callable");
        }
        $this->callback = [0 => $callback];
        return $this;
    }
}
