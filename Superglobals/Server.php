<?php

namespace PHP_Library\Superglobals;

use PHP_Library\Superglobals\PHPTraits\QueryTrait;
use PHP_Library\Superglobals\PHPTraits\ServerTrait;

/**
 * Class Server
 *
 * A class that provides easy access to $_SERVER superglobal array elements
 * as static methods. The class also overrides __toString() to return the
 * value of PHP_SELF for better readability.
 */
class Server
{
    use ServerTrait;
    use QueryTrait;

    /**
     * Checks if the current request method is POST.
     *
     * @return bool True if the request method is POST, false otherwise.
     */
    public static function has_post_request(): bool
    {
        return self::get_request_method() == 'POST';
    }

    /**
     * Converts a file path to a URL. Throws an error if the file path is out of scope.
     *
     * @param string $path The file path to convert to a URL.
     * @return string The URL to the file.
     * @throws \Error If the path is outside the document root.
     */
    public static function get_url_to_file(string $path): string
    {
        if (! self::is_serving_http())
        {
            return '';
        }
        // Absolute path
        if (str_starts_with($path, '/'))
        {
            if (!str_starts_with($path, self::get_document_root()))
            {
                throw new \Error("'$path' is out of scope for this script.");
            }
            $path = substr($path, strlen(self::get_document_root()));
        }
        // Relative path
        return self::get_root_url() . '/' . $path;
    }

    /**
     * Checks if the current HTTP request method matches a given type.
     *
     * @param string $http_request_type The HTTP request type (GET, POST, etc.) to check for.
     * @return bool True if the request method matches, false otherwise.
     */
    public static function has_http_request_type(string $http_request_type): bool
    {
        return self::get_request_method() === strtoupper($http_request_type);
    }

    /**
     * Returns the current URL being accessed, including the protocol and port.
     *
     * @return string The current URL.
     * @throws \Error If the script is not serving a request (i.e., it's running in CLI mode).
     */
    public static function get_root_url(): string
    {
        if (! self::is_serving_http())
        {
            return throw new \Error('This Script is not Serving. There is no URL');
        }
        $port = self::get_port();
        if (
            isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        )
        {
            $protocol = 'https://';
        }
        else
        {
            $protocol = 'http://';
        }

        if (!$port)
        {
            return $protocol .  self::get_server_name();
        }
        return $protocol .  self::get_server_name() . ":$port";
    }

    /**
     * Checks if the request is being made from a local environment (localhost or local IP).
     *
     * @return bool True if the request is from a local environment, false otherwise.
     */
    public static function is_local(): bool
    {
        return (
            $_SERVER['HTTP_HOST'] == 'localhost'
            || substr($_SERVER['HTTP_HOST'], 0, 3) == '10.'
            || substr($_SERVER['HTTP_HOST'], 0, 7) == '192.168'
        );
    }

    /**
     * Checks if the script is running in CLI (Command Line Interface) mode.
     *
     * @return bool True if the script is running in CLI mode, false otherwise.
     */
    public static function is_cli(): bool
    {
        return is_int(strpos(php_sapi_name(), 'cli'));
    }

    /**
     * Checks if the script is serving an HTTP request (i.e., not in CLI mode).
     *
     * @return bool True if the script is serving a request, false otherwise.
     */
    public static function is_serving_http(): bool
    {
        return ! self::is_cli();
    }

    /**
     * Alias of get_server_addr().
     * Returns the IP address of the server, or an empty string if not available.
     *
     * @return string The server's IP address, or an empty string if not serving.
     */
    public static function get_ip_address(): string
    {
        return self::get_server_addr() ?? '';
    }

    /**
     * Alias of get_server_name().
     * Returns the name of the server host, or an empty string if not available.
     *
     * @return string The server name, or an empty string if not serving.
     */
    public static function get_name(): string
    {
        return self::get_server_name() ?? '';
    }

    /**
     * Alias of get_server_port().
     * Returns the port used by the server, or an empty string if not available.
     *
     * @return string The server port, or an empty string if not serving.
     */
    public static function get_port(): string
    {
        return self::get_server_port() ?? '';
    }

    /**
     * Alias of get_server_protocol().
     * Returns the protocol used by the server (e.g., HTTP/1.1), or an empty string if not available.
     *
     * @return string The server protocol, or an empty string if not serving.
     */
    public static function get_protocol(): string
    {
        return self::get_server_protocol() ?? '';
    }

    /**
     * Alias of get_remote_addr().
     * Returns the user's IP address, or an empty string if not available.
     *
     * @return string The remote (client) IP address, or an empty string if not serving.
     */
    public static function get_remote_ip(): string
    {
        return self::get_remote_addr();
    }

    /**
     * Alias of get_server_software().
     * Returns the software used by the server, or an empty string if not available.
     *
     * @return string The server software, or an empty string if not serving.
     */
    public static function get_software(): string
    {
        return self::get_server_software();
    }
}
