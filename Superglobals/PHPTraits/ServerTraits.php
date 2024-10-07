<?php

namespace PHP_Library\Superglobals\PHPTraits;

/**
 * Trait Server
 *
 * A class that provides easy access to $_SERVER superglobal array elements
 * as static methods.
 */
trait ServerTrait
{
    /**
     * Returns the name of the server host (alternative method).
     *
     * @return string|false The value of $_SERVER['SERVER_NAME'] or false if not set.
     */
    public static function get_server_name(): string|bool
    {
        return $_SERVER['SERVER_NAME'] ?? false;
    }

    /**
     * Returns the request method used to access the page.
     *
     * @return string|false The value of $_SERVER['REQUEST_METHOD'] or false if not set.
     */
    public static function get_request_method(): string|bool
    {
        return $_SERVER['REQUEST_METHOD'] ?? false;
    }

    /**
     * Returns the IP address of the server.
     *
     * @return string|false The value of $_SERVER['SERVER_ADDR'] or false if not set.
     */
    public static function get_server_addr(): string|bool
    {
        return $_SERVER['SERVER_ADDR'] ?? false;
    }


    /**
     * Returns the port on the server used by the web server for communication.
     *
     * @return string|false The value of $_SERVER['SERVER_PORT'] or false if not set.
     */
    public static function get_server_port(): string|bool
    {
        return $_SERVER['SERVER_PORT'] ?? false;
    }

    /**
     * Returns the software used by the server.
     *
     * @return string|false The value of $_SERVER['SERVER_SOFTWARE'] or false if not set.
     */
    public static function get_server_software(): string|bool
    {
        return $_SERVER['SERVER_SOFTWARE'] ?? false;
    }

    /**
     * Returns the server protocol used to communicate (e.g., HTTP/1.1).
     *
     * @return string|false The value of $_SERVER['SERVER_PROTOCOL'] or false if not set.
     */
    public static function get_server_protocol(): string|bool
    {
        return $_SERVER['SERVER_PROTOCOL'] ?? false;
    }

    /**
     * Returns the document root directory under which the current script is executing.
     *
     * @return string|false The value of $_SERVER['DOCUMENT_ROOT'] or false if not set.
     */
    public static function get_document_root(): string|bool
    {
        return $_SERVER['DOCUMENT_ROOT'] ?? false;
    }


    /**
     * Returns the user's IP address.
     *
     * @return string|false The value of $_SERVER['REMOTE_ADDR'] or false if not set.
     */
    public static function get_remote_addr(): string|bool
    {
        return $_SERVER['REMOTE_ADDR'] ?? false;
    }

    /**
     * Returns the host name or IP address of the client sending the request.
     *
     * @return string|false The value of $_SERVER['REMOTE_HOST'] or false if not set.
     */
    public static function get_remote_host(): string|bool
    {
        return $_SERVER['REMOTE_HOST'] ?? false;
    }

    /**
     * Returns the request URI which contains the path to the requested file or resource.
     *
     * @return string|false The value of $_SERVER['REQUEST_URI'] or false if not set.
     */
    public static function get_request_uri(): string|bool
    {
        return $_SERVER['REQUEST_URI'] ?? false;
    }

    /**
     * Returns the query string if it exists in the URL.
     *
     * @return string|false The value of $_SERVER['QUERY_STRING'] or false if not set.
     */
    public static function get_query_string(): string|bool
    {
        return $_SERVER['QUERY_STRING'] ?? false;
    }

    /**
     * Returns the HTTP referer (the URL from which the request was sent).
     *
     * @return string|false The value of $_SERVER['HTTP_REFERER'] or false if not set.
     */
    public static function get_http_referer(): string|bool
    {
        return $_SERVER['HTTP_REFERER'] ?? false;
    }

    /**
     * Returns the user agent string of the client's browser.
     *
     * @return string|false The value of $_SERVER['HTTP_USER_AGENT'] or false if not set.
     */
    public static function get_http_user_agent(): string|bool
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? false;
    }

    /**
     * Returns the current script's filename.
     *
     * @return string|false The value of $_SERVER['SCRIPT_FILENAME'] or false if not set.
     */
    public static function get_script_filename(): string|bool
    {
        return $_SERVER['SCRIPT_FILENAME'] ?? false;
    }

    /**
     * Returns the current script's pathname relative to the document root.
     *
     * @return string|false The value of $_SERVER['SCRIPT_NAME'] or false if not set.
     */
    public static function get_script_name(): string|bool
    {
        return $_SERVER['SCRIPT_NAME'] ?? false;
    }

    /**
     * Returns the absolute pathname of the currently executing script.
     *
     * @return string|false The value of $_SERVER['PATH_TRANSLATED'] or false if not set.
     */
    public static function get_path_translated(): string|bool
    {
        return $_SERVER['PATH_TRANSLATED'] ?? false;
    }

    /**
     * Returns the timestamp of the start of the request.
     *
     * @return int|false The value of $_SERVER['REQUEST_TIME'] or false if not set.
     */
    public static function get_request_time(): int
    {
        return $_SERVER['REQUEST_TIME'] ?? false;
    }

    /**
     * Returns the client's port number.
     *
     * @return string|false The value of $_SERVER['REMOTE_PORT'] or false if not set.
     */
    public static function get_remote_port(): string|bool
    {
        return $_SERVER['REMOTE_PORT'] ?? false;
    }

    /**
     * Returns the current script's URI.
     *
     * @return string|false The value of $_SERVER['SCRIPT_URI'] or false if not set.
     */
    public static function get_script_uri(): string|bool
    {
        return $_SERVER['SCRIPT_URI'] ?? false;
    }
}
