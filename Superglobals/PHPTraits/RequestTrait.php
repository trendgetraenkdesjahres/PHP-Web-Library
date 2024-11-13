<?php

namespace PHP_Library\Superglobals\PHPTraits;

use PHP_Library\Error\Error;
use PHP_Library\Superglobals\Server;

trait RequestTrait
{
    use QueryTrait;

    public static array $query;
    public static string $path;
    public static array $http_header;

    public static function get_http_header(): array
    {
        return static::$http_header = array_filter($_SERVER, function ($key) {
            return strpos($key, "HTTP_") === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function get_http_header_field(string $name): string|bool
    {
        $php_server_field_name = "HTTP_" . str_replace("-", "_", strtoupper($name));
        return $_SERVER[$php_server_field_name] ?? false;
    }

    /**
     * Returns the path part of the request URI, without any query parameters.
     *
     * @return string The request URI path.
     */
    public static function get_path(): string
    {
        return static::$path = strtok(Server::get_request_uri(), '?') ?? '';
    }

    /**
     * Returns the query part of the request URI as array.
     *
     * @return array The Query data.
     */
    public static function get_query(): array
    {
        return static::$query = self::parse_query();
    }

    public static function get_query_field(string $name): int|string|array|false
    {
        if (!isset(static::$query)) {
            static::get_query();
        }
        if (!isset(static::$query[$name])) {
            return false;
        }
        return static::$query[$name];
    }
}
