<?php

namespace  PHP_Library\Router;

class Endpoint
{
    public array $properties;
    public string $endpoint;
    protected array $callback = [];
    protected string $content = '';

    /**
     * Constructor for the Endpoint l istener.
     * $response_type hhas to match a classname/filename for a Reponse type
     *
     * @param false|string $host [optional] The hostname for the API client.
     */
    public function __construct(string $endpoint, public $method = 'get', public $response_class = 'HTMLResponse')
    {
        $this->endpoint = str_starts_with($endpoint, '/') ? $endpoint : "/$endpoint";
        Router::add_endpoint($this);
    }

    /* defines what keys are known for this enpoint, mostly for the get_help */
    public function add_property(string $key, string $type = 'string', ?string $discription = null): Endpoint
    {
        if ($this->method === 'get') {
            throw new \Error("Can't accept values by post method post");
        }

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

    public function add_callback(callable $function): static
    {
        $this->callback[0] = $function;
        return $this;
    }

    public function add_content(string $content): static
    {
        $this->content .= $content;
        return $this;
    }

    public function get_content(): string
    {
        return $this->content;
    }

    public function exec_callback(mixed ...$args): mixed
    {
        if (isset($this->callback[0])) {
            return call_user_func($this->callback[0], ...$args);
        }
        return null;
    }
}
