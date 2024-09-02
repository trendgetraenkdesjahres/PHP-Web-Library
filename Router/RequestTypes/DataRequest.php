<?php

namespace  PHP_Library\Router\RequestTypes;

use PHP_Library\Router\Request;
use PHP_Library\Router\RequestInterface;
use PHP_Library\Router\AbstractResponse;
use PHP_Library\Router\ResponseTypes\JSONResponse;

/**
 * DataRequest is a specialized class for handling data requests, typically using JSON format.
 */
class DataRequest extends Request implements RequestInterface
{
    public string $type = 'Data';

    /**
     * Get a response object for data requests.
     *
     * @return AbstractResponse The JSON response object.
     */
    public function get_response(): AbstractResponse
    {
        $response = new JSONResponse($this);
        return $response;
    }
}
