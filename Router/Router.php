<?php

namespace  PHP_Library\Router;

class Router
{

    public static array $endpoints = [];

    private static Request $request;

    private static self $instance;

    public static function add_endpoint(Endpoint &$endpoint)
    {
        self::init();
        self::$endpoints[$endpoint->method][$endpoint->endpoint] = $endpoint;
    }

    public static function get_request(): Request
    {
        return self::$request;
    }

    /* if not delcared otherwise by $response_type, reponse will be same type as request. (if possible) */
    private static function get_response(Request $request, string $response_class = 'HTMLResponse')
    {
        $response_full_class_name = __NAMESPACE__ . "\\ResponseTypes\\{$response_class}";
        if ($endpoint = self::get_endpoint($request->resource_path, $request->method)) {
            if ($response_class !== $endpoint->response_class) {
                return new $response_full_class_name(
                    content: "Endpoint expected '{$endpoint->response_class}', but '{$response_class}' was requested.",
                    code: 400
                );
            }
            $content = $endpoint->exec_callback();
            if (!$content) {
                $content = $endpoint->get_content();
            }
            return new $response_full_class_name(
                content: $content,
                code: 200
            );
        } else {
            return new $response_full_class_name(
                content: 'not found',
                code: 404
            );;
        }
    }

    private function __construct()
    {
        self::$request = Request::get();
        // auth middleware
    }

    private static function init(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __destruct()
    {
        // do-something middleware
        switch (self::$request->get_type()) {
            case 'Data':
            case 'JSON':
                $response = self::get_response(self::$request, 'JSONResponse');
                break;

            case 'Form':
                $response = self::get_response(self::$request, 'HTMLResponse');
                break;

            default:
                $response = self::get_response(self::$request);
                break;
        }
        echo $response;
    }

    public static function get_endpoint(string $resource_path, string $method): Endpoint|false
    {
        if (isset(self::$endpoints[$method][$resource_path])) {
            return self::$endpoints[$method][$resource_path];
        }
        return false;
    }
}
