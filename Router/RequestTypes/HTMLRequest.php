<?php

namespace Router;

/**
 * HTMLRequest is a specialized class for handling HTTP requests that expect HTML responses.
 */
class HTMLRequest extends Request implements RequestInterface
{
    public string $type = 'HTML';

    /**
     * Get a response object for HTML requests.
     *
     * @return Response The HTML response object.
     */
    public function get_response(): Response
    {
        $response = new HTMLResponse($this);
        return $response;
    }
}
