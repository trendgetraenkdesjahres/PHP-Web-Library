<?php

namespace PHP_Library\Superglobals\PHPTraits;

use PHP_Library\Error\Warning;
use PHP_Library\Superglobals\Server;

/**
 * Trait RequestTrait
 *
 * Provides methods for handling HTTP request data, including headers, query parameters, and request paths.
 * Depends on QueryTrait for parsing query strings and the Server class for accessing server-related data.
 */
trait RequestTrait
{
    use QueryTrait;

    /**
     * @var array Stores the parsed query parameters from the request URI.
     */
    public static array $query;

    /**
     * @var string Stores the path portion of the request URI without query parameters.
     */
    public static string $path;

    /**
     * @var array Stores HTTP headers from the request, extracted from the $_SERVER superglobal.
     */
    public static array $http_header;

    /**
     * Retrieves all HTTP headers from the $_SERVER superglobal.
     *
     * @return array An associative array of HTTP headers.
     */
    public static function get_http_header(): array
    {
        return static::$http_header = array_filter($_SERVER, function ($key) {
            return strpos($key, "HTTP_") === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Retrieves a specific HTTP header by its name.
     *
     * @param string $name The name of the HTTP header (case-insensitive).
     * @return string|bool The value of the HTTP header, or false if it is not found.
     */
    public static function get_http_header_field(string $name): string|bool
    {
        $php_server_field_name = "HTTP_" . str_replace("-", "_", strtoupper($name));
        return $_SERVER[$php_server_field_name] ?? false;
    }

    /**
     * Retrieves the path portion of the request URI, excluding query parameters.
     *
     * @return string The request URI path.
     */
    public static function get_path(): string
    {
        return static::$path = strtok(Server::get_request_uri(), '?') ?? '';
    }

    /**
     * Parses and retrieves the query parameters from the request URI as an associative array.
     *
     * @return array The parsed query parameters.
     */
    public static function get_query(): array
    {
        return static::$query = self::parse_query();
    }

    /**
     * Retrieves a specific query parameter by its name.
     * Triggers a warning if the cookie is undefined.
     *
     * @param string $name The name of the query parameter.
     * @return int|string|array|false The value of the query parameter, or false if it is not set.
     */
    public static function get_query_field(string $name): int|string|array|false
    {
        if (!isset(static::$query)) {
            static::get_query();
        }

        if (!isset(static::$query[$name])) {
            Warning::trigger("Undefined Cookie Field '{$name}'");
            return false;
        }
        return static::$query[$name];
    }
}
