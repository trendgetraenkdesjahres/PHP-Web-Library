<?php

namespace Route;

class DataRequest extends Request implements RequestInterface
{
    public function get_response(): Response
    {
        $response = new JSONResponse($this);
        return $response;
    }
}
