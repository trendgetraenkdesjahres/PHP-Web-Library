<?php

namespace PHP_Library\Superglobals;

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

    public static function has_post_request(): bool
    {
        return self::get_request_method() == 'POST';
    }

    function get_url_to_file(string $path): string
    {
        //abs path
        if (str_starts_with($path, '/')) {
            if (!str_starts_with($path, self::get_document_root())) {
                throw new \Error("'$path' is out of scope for this script.");
            }
            $path = substr($path, strlen(self::get_document_root()));
        }
        // rel path
        return get_home_url() . '/' . $path;
    }

    public static function has_http_request_type(string $http_request_type): bool
    {
        return self::get_request_method() === strtoupper($http_request_type);
    }

    public static function get_current_url(): string
    {
        if (! self::is_serving()) {
            return throw new \Error('This Script is not Serving. There is no URL');
        }
        $port = self::get_port();
        if (!$port) {
            return self::get_server_protocol() .  self::get_server_name();
        }
        return self::get_server_protocol() .  self::get_server_name() . ":$port";
    }

    public static function is_local()
    {
        return (
            $_SERVER['HTTP_HOST'] == 'localhost'
            || substr($_SERVER['HTTP_HOST'], 0, 3) == '10.'
            || substr($_SERVER['HTTP_HOST'], 0, 7) == '192.168'
        );
    }

    public static function is_cli(): bool
    {
        return is_int(strpos(php_sapi_name(), 'cli'));
    }

    public static function is_serving(): bool
    {
        return ! self::is_cli();
    }

    /**
     * alias of get_server_addr()
     * Returns the IP address of the server.
     *
     * @return string|null The value of $_SERVER['SERVER_ADDR'] or null if not set.
     */
    public static function get_ip_adress(): string
    {
        return self::get_server_addr();
    }

    /**
     * Alias of get_server_name()
     * Returns the name of the server host.
     *
     * @return string|null The value of $_SERVER['SERVER_NAME'] or null if not set.
     */
    public static function get_name(): string
    {
        return self::get_server_name();
    }

    /**
     * Alias of get_server_port()
     * Returns the port on the server used by the web server for communication.
     *
     * @return string|null The value of $_SERVER['SERVER_PORT'] or null if not set.
     */
    public static function get_port(): string
    {
        return self::get_server_port() ?? null;
    }

    /**
     * Alias of get_server_protocol()
     * Returns the server protocol used to communicate (e.g., HTTP/1.1).
     *
     * @return string|null The value of $_SERVER['SERVER_PROTOCOL'] or null if not set.
     */
    public static function get_protocol(): string
    {
        return self::get_server_protocol();
    }

    /**
     * Alias of get_remote_addr()
     * Returns the user's IP address.
     *
     * @return string|null The value of $_SERVER['REMOTE_ADDR'] or null if not set.
     */
    public static function get_remote_ip(): string
    {
        return self::get_remote_addr();
    }

    /**
     * Alias of get_server_software()
     * Returns the software used by the server.
     *
     * @return string|null The value of $_SERVER['SERVER_SOFTWARE'] or null if not set.
     */
    public static function get_software(): string
    {
        return self::get_server_software();
    }
}
