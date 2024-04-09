<?php

namespace  PHP_Library\Router\RequestTypes;

/**
 * FormRequest is a specialized class for handling form submissions.
 */
class FormRequest extends Request implements RequestInterface
{
    public string $type = 'Form';
    /**
     * Get a response object for form requests.
     *
     * @return Response The HTML response object.
     */
    public function get_response(): Response
    {
        $response = new HTMLResponse($this);
        return $response;
    }
}