<?php

namespace PHP_Library\HTTP\HTTPClient\Auth;


class Basic extends AbstractAuth
{
    public readonly string $password;

    public function __construct(string $username, string $password)
    {
        $this->client_id = $username;
        $this->password = $password;
    }

    public function get_query_params(): array
    {
        return [];
    }

    public function get_header_fields(): array
    {
        return ['Authorization' => 'Basic ' . base64_encode("{$this->client_id}:{$this->password}")];
    }
}
