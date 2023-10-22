<?php

namespace Router;

/**
 * JSONRequest is a specialized class for handling HTTP requests that expect JSON responses.
 */
class JSONRequest extends Request implements RequestInterface
{
    /**
     * Get a response object for JSON requests.
     *
     * @return Response The JSON response object.
     */
    public function get_response(): Response
    {
        $response = new JSONResponse($this);
        return $response;
    }
}
