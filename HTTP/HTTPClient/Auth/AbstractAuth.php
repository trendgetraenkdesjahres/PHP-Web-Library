<?php

namespace PHP_Library\HTTP\HTTPClient\Auth;

abstract class AbstractAuth {
    protected string $client_id;
    public function __construct()
    {
        
    }

    abstract function get_query_params(): array ;
}