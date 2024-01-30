<?php

namespace Library\Router;

class Endpoint
{
    public array $properties;
    public array $callbacks;
    /**
     * Constructor for the Endpoint listener.
     *
     * @param false|string $host [optional] The hostname for the API client.
     */
    public function __construct(public ?string $endpoint = null, public $method = 'get')
    {
        if ($endpoint) {
            if (!is_int(strpos($endpoint, $_SERVER["SERVER_NAME"]))) {
                $endpoint = $_SERVER["SERVER_NAME"] . (str_starts_with($endpoint, '/') ? $endpoint : "/$endpoint");
                $this->endpoint = $endpoint;
            }
        }
    }

    /* defines what keys are known for this enpoint, mostly for the get_help */
    public function add_property(string $key, string $type = 'string', ?string $discription = null): Endpoint
    {
        $key_name_regex = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';
        if (!is_int(preg_match_all($key_name_regex, $key))) {
            throw new \Error("'$key' is not a valid key.");
        }
        $php_types = [
            "boolean",
            "integer",
            "double",
            "string",
            "array",
            "object",
            "resource",
            "null"
        ];
        if (!in_array($type, $php_types)) {
            throw new \Error("'$type' is not a valid php $type.");
        }
        $this->properties[$key] = [
            'type' => $type,
            'discription' => $discription,
        ];
        return $this;
    }

    public function add_callback(callable $function)
    {
        array_push($this->callbacks, $function);
    }
}
