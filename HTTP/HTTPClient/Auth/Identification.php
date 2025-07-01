<?php
namespace PHP_Library\HTTP\HTTPClient\Auth;

use PHP_Library\HTTP\HTTPClient\AbstractAuth;

class Identification extends AbstractAuth{

    protected readonly string $identifier_name;

    public function __construct(string $identifier, string $identifier_name = 'client_id') {
        $this->client_id = $identifier;
        $this->identifier_name = $identifier_name;
    }

    public function get_query_params(): array
    {
        return [$this->identifier_name => $this->client_id];
    }
}