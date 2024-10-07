<?php

namespace  PHP_Library\Router\RequestTypes;

use PHP_Library\Router\Request;
use PHP_Library\Router\RequestInterface;
use PHP_Library\Router\Response\AbstractResponse;
use PHP_Library\Router\Response\HTMLResponse;

/**
 * HTMLRequest is a specialized class for handling HTTP requests that expect HTML responses.
 */
class HTMLRequest extends Request implements RequestInterface
{
    public string $type = 'HTML';

    /**
     * Get a response object for HTML requests.
     *
     * @return AbstractResponse The HTML response object.
     */
    public function get_response(): Response
    {
        $response = new HTMLResponse($this);
        return $response;
    }
}
