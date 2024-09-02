<?php

namespace  PHP_Library\Router\Response;

use PHP_Library\Router\Response\Traits\HTTPText;

/**
 * JSONResponse is a specialized class for handling JSON responses.
 */
class JSONResponse extends AbstractResponse
{
    use HTTPText;
    public array $header =  ['Content-Type: application/json'];

    /**
     * Set the response body for JSON responses.
     *
     * @param string $body The body content to set.
     *
     * @return static The response object.
     */
    public function set_body(mixed $content): static
    {
        $this->body = json_encode($content);
        return $this;
    }
}
