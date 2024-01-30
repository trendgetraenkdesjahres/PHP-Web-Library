<?php

namespace Library\Router\RequestTypes;

use Library\Router\Request;
use Library\Router\RequestInterface;

/**
 * DataRequest is a specialized class for handling data requests, typically using JSON format.
 */
class DataRequest extends Request implements RequestInterface
{
    public string $type = 'Data';

    /**
     * Get a response object for data requests.
     *
     * @return Response The JSON response object.
     */
    public function get_response(): Response
    {
        $response = new JSONResponse($this);
        return $response;
    }
}
