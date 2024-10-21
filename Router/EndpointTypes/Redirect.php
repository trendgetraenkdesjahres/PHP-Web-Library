<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Error\Error;
use PHP_Library\Router\Endpoint;

class Redirect extends Endpoint
{
    private string $location;

    public function get_content(): string
    {
        return '';
    }

    protected function constructor(mixed $location): static
    {
        $this->status_code = 302;
        if (
            is_object($location)
            && is_subclass_of($location, get_parent_class($this))
        ) {
            $this->http_headers['location'] = $location->path;
            return $this;
        }
        if (!is_string($location)) {
            throw new Error("\$location is not a string");
        }
        $this->http_headers['location'] = $location;
        return $this;
    }
}
