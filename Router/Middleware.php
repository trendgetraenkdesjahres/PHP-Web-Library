<?php

namespace  PHP_Library\Router;

class Endpoint
{

    private array $registred_data_keys = [];
    /**
     * Constructor for the Endpoint listener.
     *
     * @param false|string $host [optional] The hostname for the API client.
     */
    public function __construct(public ?string $endpoint = null)
    {
        if ($endpoint) {
            if (!is_int(strpos($endpoint, $_SERVER["SERVER_NAME"]))) {
                $endpoint = $_SERVER["SERVER_NAME"] . (str_starts_with($endpoint, '/') ? $endpoint : "/$endpoint");
                $this->endpoint = $endpoint;
            }
        }
    }

    /* defines what keys are known for this enpoint, mostly for the get_help */
    public function register_data_key(string $key, string $type = 'string', ?string $discription = null): Endpoint
    {
        array_push($this
        ->registred_data_keys, [
            $key => [
                'type' =>
            ]
        ])
    }

    public function get_help(): string
    {
    }

    /**
     * Set the endpoint for Middleware Action.
     *
     * @param string $endpoint The endpoint.
     *
     * @return Middleware Returns the current instance of the Middleware.
     */
    public function set_endpoint_url(string $endpoint): Middleware
    {
        if (is_int(strpos($target, '://'))) {
            $this->target_url = $target . ($query_parameters ? '?' . http_build_query(data: $query_parameters) : '');
            return $this;
        }
        if (isset($this) && isset($this->host)) {
            $this->target_url = $this->host . $target . ($query_parameters ? '?' . http_build_query(data: $query_parameters) : '');
            return $this;
        }
        PHP_Library\Warning::trigger("Neither \$target ('$target') is valid nor \$this->host is set.");
        return $this;
    }

    /**
     * Perform a GET request.
     *
     * @param array $http_headers [optional] An array of HTTP headers.
     * @param array $curl_options [optional] Additional cURL options.
     * @param false|string $target_url [optional] The target URL for the request.
     *
     * @return APIClient Returns the current instance of the API client.
     */
    public function set_post_data(array $http_headers = [], array $curl_options = [], false|string $target_url = false): APIClient
    {
        if (!$target_url) {
            if (!$this->target_url) {
                $this->target_url = $this->host;
                Notice::trigger("No \$target_url is set up or given in \$this->target_url, will use '$this->host'");
            }
            $target_url = $this->target_url;
        }
        if ($this->curl = $this->prepare_curl(
            url: $target_url,
            http_headers: $http_headers,
            curl_options: $curl_options
        )) {
            try {
                $this->exec_curl($this->curl);
            } catch (Warning $e) {
                PHP_Library\Warning::trigger($e->getMessage());
            }
        }
        return $this;
    }
}
