<?php

namespace  PHP_Library\Router;

use PHP_Library\ClassTraits\SingletonPattern;
use PHP_Library\Router\EndpointTypes\Redirect;
use PHP_Library\Router\Error\RouterError;
use PHP_Library\Router\HTMLResponse\HTMLDoc;
use PHP_Library\Superglobals\Get;
use PHP_Library\Superglobals\Server;

class Router
{
    use SingletonPattern;

    public static array $endpoints = [];
    protected static array $html_templates = [];
    public static Endpoint $current_endpoint;

    public static function add_endpoint(Endpoint &$endpoint)
    {
        self::init_singleton();
        $method = strtoupper($endpoint->http_method);
        if (isset(self::$endpoints[$method][$endpoint->path])) {
            throw new RouterError("Endpoint {$method} '{$endpoint}' is already set.");
        }
        self::$endpoints[$method][$endpoint->path] = $endpoint;
    }

    public static function add_html_template(string $path, string $regex = ".*")
    {
        self::$html_templates[$regex] = $path;
    }

    public static function redirect_now(string $location, array $query_data = []): void
    {
        if (headers_sent()) {
            throw new RouterError("Can not trigger redirect after headers been sent.");
        }
        $redirect_endpoint = new Redirect(Router::$current_endpoint->path, $location);
        $redirect_endpoint->add_query_data($query_data);
        static::$current_endpoint = $redirect_endpoint;
        die();
    }

    private function __construct()
    {
        if (Server::is_serving_http()) {
            header_register_callback(function () {
                static::php_header_callback();
            });
        }
    }

    public function __destruct()
    {
        $content = static::current_endpoint()->get_content();
        $status_code = static::current_endpoint()->status_code;
        if ($content === false) {
            $content = "Endpoint registred but no get_content().";
            $status_code = 500;
        }
        self::send_status_code($status_code);
        print(self::decode_content($content));
        exit();
    }

    protected static function decode_content(mixed $content): string
    {
        $client_accept_header = explode(',', Get::get_http_header_field('accept'));
        switch ($client_accept_header[0]) {
            case 'text/html':
                return self::get_html_doc($content);
                break;

            case 'application/json':
                return json_encode($content);

            case 'application/xml':
                return xmlrpc_encode($content);

            default:
                return (string) $content;
        }
    }

    protected static function get_html_doc($content): string
    {
        foreach (self::$html_templates as $regex => $path) {
            if (preg_match($regex, Get::get_path())) {
                HTMLDoc::set_template_file($path);
            }
        }
        return HTMLDoc::get_rendered($content);
    }

    protected static function php_header_callback()
    {
        foreach (static::current_endpoint()->http_headers as $field => $value) {
            if (is_array($value)) {
                $value = rtrim(implode(';', $value), ";");
            }
            header("$field: $value");
        }
    }

    private static function current_endpoint(): Endpoint
    {
        if (isset(self::$current_endpoint)) {
            return self::$current_endpoint;
        }
        $path = Get::get_path();
        $method = Server::get_request_method();
        if (! isset(self::$endpoints[$method][$path])) {
            throw new RouterError("No Route for $method '$path' defined.");
        }
        self::$current_endpoint = self::$endpoints[$method][$path];
        return self::$current_endpoint;
    }

    private static function send_status_code(int $code, string $message = ''): void
    {
        if (headers_sent()) {
            throw new RouterError("Headers already sent.");
        }
        http_response_code($code);
        header(Server::get_protocol() . " " . trim("$code $message"));
    }
}
