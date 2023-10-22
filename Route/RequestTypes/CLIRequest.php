<?php

namespace Route;

class CLIRequest extends Request implements RequestInterface
{
    public function get_response(): Response
    {
        $response = new CLIResponse($this);
        return $response;
    }
}
