<?php

namespace  PHP_Library\Router\RequestTypes;

use PHP_Library\Router\Request;
use PHP_Library\Router\RequestInterface;
use PHP_Library\Router\Response;
use PHP_Library\Router\ResponseTypes\CLIResponse;

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
