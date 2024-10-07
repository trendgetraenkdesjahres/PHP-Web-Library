<?php

namespace  PHP_Library\Router;

use PHP_Library\Router\RequestTypes\CLIRequest;
use PHP_Library\Router\RequestTypes\DataRequest;
use PHP_Library\Router\RequestTypes\FormRequest;
use PHP_Library\Router\RequestTypes\HTMLRequest;
use PHP_Library\Router\RequestTypes\JSONRequest;
use PHP_Library\Router\Response\AbstractResponse;
use PHP_Library\Superglobals\Server;

/**
 * RequestInterface defines the methods that should be implemented by request classes.
 */
interface RequestInterface
{
    public function get_response(): AbstractResponse;
    public function get_method(): string|null;
    public function get_data(): array|null;
    public function get_resource_path(): string|null;
}

/**
 * Request is the base class for handling different types of requests.
 */
class Request
{
    public string $type = '';
    /**
     * Factory method to create a Request object based on the request type.
     *
     * @return Request The request object.
     */
    public static function get(): static
    {
        if (Server::is_cli()) {
            return new CLIRequest();
        }

        if (Server::has_post_request()) {
            switch (Server::get_post_request_content_type()) {
                case 'application/json':
                    return new JSONRequest(
                        method: 'post',
                        resource_path: Server::get_request_uri_path(),
                        data: json_decode(
                            json: file_get_contents("php://input"),
                            associative: true
                        )
                    );

                case 'multipart/form-data':
                    return new DataRequest(
                        method: 'post',
                        resource_path: Server::get_request_uri_path(),
                        data: array_merge($_POST, $_FILES)
                    );

                case 'application/x-www-form-urlencoded':
                    return new FormRequest(
                        method: 'post',
                        resource_path: strtok($_SERVER["REQUEST_URI"], '?'),
                        data: self::get_query_array(
                            query: file_get_contents("php://input")
                        )
                    );
            }
        }

        if (
            isset($_SERVER['HTTP_ACCEPT'])
            && is_int(strpos($_SERVER['HTTP_ACCEPT'], 'application/json'))
        ) {
            $query = self::get_query_array();
            return new JSONRequest(
                method: 'get',
                resource_path: strtok($_SERVER["REQUEST_URI"], '?'),
                data: $query
            );
        }

        $query = self::get_query_array();
        return new HTMLRequest(
            method: 'get',
            resource_path: strtok($_SERVER["REQUEST_URI"], '?'),
            data: $query
        );
    }

    /**
     * Get the HTTP request method.
     *
     * @return string|null The HTTP request method.
     */
    public function get_method(): string|null
    {
        return $this->method;
    }

    /**
     * Get the request data.
     *
     * @return array|null The request data.
     */
    public function get_data(): array|null
    {
        return $this->data;
    }

    /**
     * Get the type of request.
     *
     * @return array|null The request data.
     */
    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * Get the resource path/ request-uri without query.
     *
     * @return string|null The resource path.
     */
    public function get_resource_path(): string|null
    {
        return $this->resource_path;
    }

    /**
     * Parse a query string into an array.
     *
     * @param string [optional] $query The query string to parse. If not given, it parses $_SERVER["QUERY_STRING"].
     *
     * @return array|null The parsed query as an array.
     */
    private static function get_query_array(?string $query = null): array|null
    {
        if (!$query) {
            $query = $_SERVER["QUERY_STRING"];
        }
        if ($query) {
            $return = [];
            $array = explode('&', $query);
            foreach ($array as $value) {
                if (strpos($value, '=')) {
                    $key_value = explode('=', $value);
                    $return[$key_value[0]] = $key_value[1];
                } else {
                    $return[$value] = true;
                }
            }
            return $return;
        }
        return null;
    }

    /**
     * Constructor to initialize properties.
     *
     * @param string|null $method        The HTTP request method.
     * @param string|null $resource_path The resource path.
     * @param array|null  $data          The request data.
     */
    public function __construct(public ?string $method = null, public ?string $resource_path = null, public ?array $data = null) {}
}
