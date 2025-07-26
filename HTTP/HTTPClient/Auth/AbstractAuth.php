<?php

namespace PHP_Library\HTTP\HTTPClient\Auth;

use PHP_Library\HTTP\HTTPClient\HTTP1Client;

abstract class AbstractAuth
{
    protected string $client_id;

    // scheme + host + port
    protected readonly string $default_origin;

    public function __construct() {}
    abstract function get_query_params(): array;
    abstract function get_header_fields(): array;

    public function set_default_host(string $origin): static {
        if(filter_var($origin, FILTER_VALIDATE_URL)) {
            $url_parts = parse_url($origin);
            $origin = HTTP1Client::build_url($url_parts['scheme'], $url_parts['host'], $url_parts['port'] ?? null);
        }
        $this->default_origin = $origin;
        return $this;
    }
}
