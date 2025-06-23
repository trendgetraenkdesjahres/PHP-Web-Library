<?php

namespace PHP_Library\HTTP\HTTPResponse;

use PHP_Library\HTTP\HTTPMessage\HTTPHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPMessage;

class HTTPResponse extends HTTPMessage
{
    public readonly string $http_version;
    public readonly int $status_code;
    public readonly string $reason_phrase;

    public function __construct($http_version, $status_code, $reason_phrase, ?HTTPHeader $header, ?string $body)
    {
        parent::__construct("$http_version $status_code $reason_phrase", $header, $body);
        $this->http_version = $http_version;
        $this->status_code = $status_code;
        $this->reason_phrase = $reason_phrase;
    }

    public static function from_raw(string $http_data): static
    {
        $response = parent::from_raw($http_data);
        $status_line = explode(" ", $response->start_line);
        $response->http_version =  array_shift($status_line);
        $response->status_code =  array_shift($status_line);
        $response->reason_phrase =  trim(implode(" ",$status_line));
        return $response;
    }

        public function find(string $regex_pattern): array {
        if(!$this->raw_body) {
            return [];
        }
        $results = [];
        preg_match_all($regex_pattern, $this->raw_body, $results);
        return $results;
    }
}
