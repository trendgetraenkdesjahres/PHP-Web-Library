<?php

namespace Library\Router\RequestTypes;

use Library\Router\Request;
use Library\Router\RequestInterface;

/**
 * CLIRequest is a specialized class for handling command-line interface (CLI) requests.
 */
class CLIRequest extends Request implements RequestInterface
{
    public string $type = 'CLI';

    /**
     * Get a response object for CLI requests.
     *
     * @return Response The CLI response object.
     */
    public function get_response(): Response
    {
        $response = new CLIResponse($this);
        return $response;
    }
}
