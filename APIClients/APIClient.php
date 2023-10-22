<?php

namespace APIClients;

use \CurlHandle;
use Notices\Notice;
use Notices\Warning;

class APIClient
{

    /**
     * Constructor and Initialization:
     *
     * This section of the code is responsible for setting up the foundational elements of the `APIClient` class. The constructor (`__construct`) initializes the API client, allowing developers to specify the API host and define whether JSON responses are expected. This pivotal step ensures that the client is ready for making HTTP requests and interacting with external APIs.
     *
     * Additionally, the `set_target_url` method offers the flexibility to define the target URL for API requests, including optional query parameters.
     */

    protected string $access_token;
    public string $host;
    public string|array $curl_response_body = '';
    public int $curl_response_code = 0;
    protected null|string $target_url = null;
    protected bool $talking_json = true;
    private CurlHandle $curl;
    private array $result_body_filters = [];
    private array $result_array_filters = [];

    /**
     * Constructor for the API client.
     *
     * @param false|string $host [optional] The hostname for the API client.
     * @param bool $talking_json [optional] If true, expects JSON responses.
     */
    public function __construct(false|string $host = false, bool $talking_json = true)
    {;
        if ($host) $this->host = (substr($host, -1) != '/') ? "$host/" : $host;
        $this->talking_json = $talking_json;
    }

    /**
     * Set the target URL for the API request.
     *
     * @param string $target The target URL or endpoint.
     * @param false|array $query_parameters [optional] An array of query parameters.
     *
     * @return APIClient Returns the current instance of the API client.
     */
    public function set_target_url(string $target = '', ?array $query_parameters = null): APIClient
    {
        if (is_int(strpos($target, '://'))) {
            $this->target_url = $target . ($query_parameters ? '?' . http_build_query(data: $query_parameters) : '');
            return $this;
        }
        if (isset($this) && isset($this->host)) {
            $this->target_url = $this->host . $target . ($query_parameters ? '?' . http_build_query(data: $query_parameters) : '');
            return $this;
        }
        Warning::trigger("Neither \$target ('$target') is valid nor \$this->host is set.");
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
    public function http_get(array $http_headers = [], array $curl_options = [], false|string $target_url = false): APIClient
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
                Warning::trigger($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Perform a POST request.
     *
     * @param false|array $post_fields [optional] An array of POST fields.
     * @param array $http_headers [optional] An array of HTTP headers.
     * @param array $curl_options [optional] Additional cURL options.
     * @param false|string $target_url [optional] The target URL for the request.
     *
     * @return APIClient Returns the current instance of the API client.
     */
    public function http_post(?array $post_fields = null, array $http_headers = [], array $curl_options = [], false|string $target_url = false): APIClient
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
            curl_options: $curl_options,
            post_fields: $post_fields
        )) {
            $this->exec_curl($this->curl);
        }
        return $this;
    }

    /**
     * cURL Handling and Execution:
     *
     * This section of the code is dedicated to the management of cURL requests and their execution. It encompasses methods responsible for preparing and executing cURL requests, handling potential errors, and processing the responses. These functions are vital for making HTTP requests and dealing with the intricacies of external APIs.
     *
     * - `prepare_curl`: Prepares the cURL handle with the necessary configurations for an HTTP request.
     * - `exec_curl`: Executes a cURL request, handles errors, and processes the response, ensuring that the data is retrieved and prepared for further use.
     */

    /**
     * Prepare the cURL handle for an HTTP request.
     *
     * @param string $url The URL for the request.
     * @param array $http_headers [optional] An array of HTTP headers.
     * @param array $curl_options [optional] Additional cURL options.
     * @param false|array $post_fields [optional] An array of POST fields.
     *
     * @return false|CurlHandle Returns the cURL handle or false on error.
     */
    private function prepare_curl(string $url, array $http_headers = [], array $curl_options = [], false|array $post_fields = false): false|CurlHandle
    {
        $curl  = curl_init();

        if ($this->talking_json) array_unshift($http_headers, 'Content-Type: application/json');

        if (isset($this->access_token)) array_unshift(
            $http_headers,
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token
        );

        $sanitizing_http_headers = [];
        foreach ($http_headers as $field_name => $field_value) {
            if (is_string($field_name)) {
                $sanitizing_http_headers[$field_name] = $field_value;
                continue;
            }
            if ($field_pair = explode(":", $field_value, 2)) {
                $sanitizing_http_headers[$field_pair[0]] = $field_pair[1];
                continue;
            }
            Warning::trigger("'$field_value' is not a valid http header.");
        }
        $sanitized_http_headers = [];
        foreach ($sanitizing_http_headers as $field_name => $field_value) {
            array_push($sanitized_http_headers, trim($field_name) . ": " . trim($field_value));
        }
        if (is_array($post_fields)) {
            $curl_options = [
                CURLOPT_POST           => TRUE,
                CURLOPT_POSTFIELDS     => count($post_fields) > 1 ? json_encode($post_fields) : $post_fields[array_key_first($post_fields)]
            ] + (array) $curl_options;
        }
        $curl_options = array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => TRUE,
            CURLOPT_FOLLOWLOCATION  => TRUE,
            CURLOPT_ENCODING        => '',
            CURLOPT_MAXREDIRS       => 5,
            CURLOPT_TIMEOUT         => 5,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER      => $sanitized_http_headers,
            CURLOPT_CONNECTTIMEOUT  => 5
        ) + (array) $curl_options;
        curl_setopt_array($curl, $curl_options);

        return $curl;
    }

    /**
     * Execute a cURL request, handle errors, and process the response.
     *
     * @param CurlHandle $curl_handle The cURL handle.
     *
     * @return mixed The response data or false on error.
     */

    private function exec_curl(CurlHandle $curl_handle): mixed
    {
        $curl_result = curl_exec($curl_handle);
        if ($curl_result === false) {
            $errno = curl_errno($curl_handle);
            $error = curl_error($curl_handle);
            Warning::trigger("Curl returned error $errno: $error\n");
            $this->curl_response_body = false;
            return false;
        }
        curl_close($curl_handle);

        $curl_result_http_code = (int) curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        switch ($curl_result_http_code) {
            case $curl_result_http_code >= 500:
                throw new Warning($this->get_response_info(CURLINFO_EFFECTIVE_URL) . " returned $curl_result_http_code error");
                $this->curl_response_body = false;
                $this->curl_response_code = $curl_result_http_code;
                break;

            case $curl_result_http_code != 200:
                if ($this->talking_json) {
                    $response = json_decode(
                        json: $curl_result,
                        associative: true
                    );
                    if (!$response) {
                        break;
                    }

                    switch ($response) {
                        case isset($response['description']):
                            $description = "( {$response['description']} )";
                            break;

                        default:
                        case isset($response['error']):
                            $description = "( " . ($response['error']['message'] ?? $response['error_description'] ?? $response['error']) . ")";
                            break;
                    }
                }
                throw new Warning(
                    message: $this->get_response_info(CURLINFO_EFFECTIVE_URL) . " returned $curl_result_http_code error " . (isset($description) ? $description : '')
                );
                $this->curl_response_body = false;
                $this->curl_response_code = $curl_result_http_code;
                break;

            default:
                if (count($this->result_body_filters) > 0) {
                    foreach ($this->result_body_filters as $callable_filter) {
                        $curl_result = filter_var(
                            value: $curl_result,
                            filter: FILTER_CALLBACK,
                            options: ['options' => $callable_filter]
                        );
                    }
                }
                if ($this->talking_json) {
                    $json_response = json_decode(
                        json: $curl_result,
                        associative: true
                    );
                    $json_response = isset($json_response['result']) ? $json_response['result'] : $json_response;
                    if (!is_array($json_response)) {
                        throw new Warning("Response was not json. instead it was: '" . (string) $json_response . "'");
                    }
                    if (count($this->result_array_filters) > 0) {
                        foreach ($this->result_array_filters as $callable_filter) {
                            $json_response = call_user_func(
                                $callable_filter,
                                $json_response
                            );
                        }
                    }
                }
                $this->curl_response_body = isset($json_response) ? $json_response : $curl_result;
                $this->curl_response_code = $curl_result_http_code;
                break;
        }
        return $this;
    }

    /**
     * Response Processing and Filtering:
     *
     * This section of the code is dedicated to handling, processing, and filtering responses from HTTP requests. It includes methods for filtering and modifying both the response body and the resulting arrays after JSON conversion. These functions are essential for post-processing data retrieved from external APIs.
     *
     * - `add_result_body_filter`: Registers callback functions to filter the response body before JSON conversion.
     * - `add_result_array_filter`: Registers callback functions to filter the response array after JSON conversion.
     * - `get_response_body`: Retrieves the response body, ensuring it's obtained through an HTTP GET request and handling potential errors.
     * - `get_response_info`: Fetches information about the cURL request, allowing access to various cURL options and details.
     */

    /**
     * Register a callback to filter the response body before JSON conversion.
     *
     * @param callable $callable_filter The callback function for filtering.
     *
     * @return APIClient Returns the current instance of the API client.
     */
    public function add_result_body_filter(callable $callable_filter): APIClient
    {
        if (is_callable($callable_filter)) {
            array_push($this->result_body_filters, $callable_filter);
        } else {
            throw new Error("$callable_filter is not callable.");
        }
        return $this;
    }

    /**
     * Register a callback to filter the response array after JSON conversion.
     *
     * @param callable $callable_filter The callback function for filtering.
     *
     * @return APIClient Returns the current instance of the API client.
     */
    public function add_result_array_filter(callable $callable_filter): APIClient
    {
        if (is_callable($callable_filter)) {
            array_push($this->result_array_filters, $callable_filter);
        } else {
            throw new \Error("$callable_filter is not callable.");
        }
        return $this;
    }

    /**
     * Get the response body, making an HTTP GET request if needed.
     *
     * @return array|string|false The response data or false if an error occurred.
     */
    public function get_response_body(): array|string|false
    {
        if (!$this->curl_response_body) {
            $this->http_get();
        }
        if ($this->curl_response_body) {
            return $this->curl_response_body;
        }
        throw new Warning("curl_response_body is emtpy.");
        return false;
    }

    /**
     * Get information about the cURL request.
     *
     * @param int $opt The cURL option to retrieve information for.
     *
     * @return mixed The requested information.
     */
    public function get_response_info(int $opt)
    {
        return curl_getinfo($this->curl, $opt);
    }
}
