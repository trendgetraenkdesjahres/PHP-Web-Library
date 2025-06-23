<?php

namespace PHP_Library\HTTP\HTTP1Request;

use PHP_Library\HTTP\HTTPMessage\HTTPHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPMessage;

class HTTP1Request extends HTTPMessage
{
    public readonly string $method;
    public readonly string $request_uri;
    public readonly string $http_version;

    public function __construct(string $method, string $request_uri = "/", string $http_version = "HTTP/1.1", ?HTTPHeader $header = null, string $body = '')
    {
        parent::__construct("$method $request_uri $http_version", $header, $body);
        $this->method = $method;
        $this->request_uri = $request_uri;
        $this->http_version = $http_version;
    }

    public function __debugInfo()
    {
        $info = [];
        return array_merge(parent::__debugInfo(), $info);
    }

    public static function from_raw(string $http_data): static
    {
        $request = parent::from_raw($http_data);
        $request_line = explode(" ", $request->start_line);
        $request->method =  array_shift($request_line);
        $request->request_uri =  array_shift($request_line);
        $request->http_version =  array_shift($request_line);
        return $request;
    }
}
