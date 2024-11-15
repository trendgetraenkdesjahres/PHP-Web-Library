<?php

namespace PHP_Library\Router\EndpointTypes;

use PHP_Library\Error\Error;
use PHP_Library\Router\Endpoint;
use PHP_Library\Router\Error\RouterError;

/**
 * the 'content' is the location, the 'path' is the path. so we redirect from the path to the location.
 * @package PHP_Library\Router\EndpointTypes
 */
class Redirect extends Endpoint
{
    public function get_content(): string
    {
        return '';
    }

    protected function constructor(mixed $location): static
    {
        $this->status_code = 302;
        if (is_object($location)) {
            if (is_subclass_of($location, get_parent_class($this))) {
                $location =  $location->path;
            } else {
                throw new RouterError("\$location is not an Endpoint");
            }
        } elseif (!is_string($location)) {
            throw new RouterError("\$location is not a string");
        }
        $this->http_headers['location'] = $location;
        return $this;
    }

    public function add_query_data(array $data): static
    {
        if (empty($data)) {
            return $this;
        }
        $url = $this->http_headers['location'];
        if (is_int(strpos($url, '?'))) {
            $url .= "&" . http_build_query($data);
        } else {
            $url .= "?" . http_build_query($data);
        }
        $this->http_headers['location'] = $url;
        return $this;
    }
}
