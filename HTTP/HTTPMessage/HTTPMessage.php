<?php

namespace PHP_Library\HTTP\HTTPMessage;

use ReflectionClass;

abstract class HTTPMessage
{
    public string $start_line;
    public ?HTTPHeader $header;
    public string $raw_body;

    public function __construct(string $start_line, ?HTTPHeader $header, string $body = '')
    {
        $this->start_line = $start_line;
        $this->header = $header;
        $this->raw_body = $body;
    }

    public function __debugInfo()
    {
        return [
            'start_line' => $this->start_line,
            'header' => $this->header->to_array(),
            'raw_body' => $this->raw_body
        ];
    }

    public static function from_raw(string $http_data): static
    {
        $http_data_parts = preg_split("/\R{2,}/", $http_data);
        $body = $http_data_parts[1] ??  null;
        $header_data = explode(PHP_EOL, $http_data_parts[0]);
        $header_data = array_filter($header_data, function ($value) {
            return (bool) $value;
        });
        if (! $start_line = array_shift($header_data)) {
            throw new \Error("Error parsing http data");
        }
        $header = $http_data ? new HTTPHeader($header_data) : null;
        $reflection = new ReflectionClass(static::class);
        $jar = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('start_line')->setValue($jar, $start_line);
        $reflection->getProperty('header')->setValue($jar, $header);
        $reflection->getProperty('raw_body')->setValue($jar, $body);
        return $jar;
    }
}
