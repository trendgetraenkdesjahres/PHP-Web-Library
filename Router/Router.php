<?php

namespace  PHP_Library\Router;

use PHP_Library\Router\Response\AbstractResponse;

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
    private static function response(Request $request)
    {
        $endpoint = self::get_endpoint($request->resource_path, $request->method);
        self::response_if(
            condition: !$endpoint,
            content: "Not found",
            code: 404
        );
        if ($endpoint->response_class == 'File') {
            self::create_response($endpoint->get_content(), 200, 'File')->articulate();
            die();
        }

        $content = $endpoint->exec_callback();
        if (!$content) {
            $content = $endpoint->get_content();
        }
        self::create_response($content, 200)->articulate();
        die();
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
        self::response(self::$request);
    }

    protected static function response_if(bool $condition, string $content, int $code)
    {
        if ($condition) {
            $response = self::create_response(
                content: "Not found",
                code: 404
            );
            $response->articulate();
            die();
        }
    }

    private static function create_response(mixed $content, int $code, ?string $endpoint_response_class = null): AbstractResponse
    {
        if ($endpoint_response_class === 'File') {
            $type = 'File';
        } else {
            $type = self::get_request()->get_type();
        }
        $response_class = __NAMESPACE__ . "\\Response\\{$type}Response";
        return new $response_class($content, $code);
    }

    public static function get_endpoint(string $resource_path, string $method): Endpoint|false
    {
        if (isset(self::$endpoints[$method][$resource_path])) {
            return self::$endpoints[$method][$resource_path];
        }
        return false;
    }
}
