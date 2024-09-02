<?php

namespace  PHP_Library\Router\RequestTypes;

use PHP_Library\Router\Request;
use PHP_Library\Router\RequestInterface;
use PHP_Library\Router\AbstractResponse;

/**
 * CLIRequest is a specialized class for handling command-line interface (CLI) requests.
 */
class CLIRequest extends Request implements RequestInterface
{
    public string $type = 'CLI';

    /**
     * Get a response object for CLI requests.
     *
     * @return AbstractResponse The CLI response object.
     */
    public function get_response(): AbstractResponse
    {
        $response = new CLIResponse($this);
        return $response;
    }
}
