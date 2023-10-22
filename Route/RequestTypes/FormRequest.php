<?php

namespace Route;

class FormRequest extends Request implements RequestInterface
{
    public function get_response(): Response
    {
        $response = new HTMLResponse($this);
        return $response;
    }
}
