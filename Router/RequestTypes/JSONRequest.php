<?php

namespace  PHP_Library\Router\RequestTypes;

use PHP_Library\Router\Request;
use PHP_Library\Router\RequestInterface;
use PHP_Library\Router\AbstractResponse;
use PHP_Library\Router\ResponseTypes\JSONResponse;

/**
 * JSONRequest is a specialized class for handling HTTP requests that expect JSON responses.
 */
class JSONRequest extends Request implements RequestInterface
{
    public string $type = 'JSON';

    /**
     * Get a response object for JSON requests.
     *
     * @return AbstractResponse The JSON response object.
     */
    public function get_response(): AbstractResponse
    {
        $response = new JSONResponse($this);
        return $response;
    }
}
