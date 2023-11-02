<?php

namespace Router;

require_once 'Response.php';
require_once 'Request.php';

class Router
{

    public static array $post_endpoints = [];

    private static Request $request;

    /* if not delcared otherwise by $response_type, reponse will be same type as request. (if possible) */
    private static function get_response(Request $request, ?string $response_type = null): Response
    {
        $namespace_parts = explode('\\', get_class($request));
        $request_type = array_pop($namespace_parts);
        if (!$response_type) {
            $response_type = str_replace(
                search: 'Request',
                replace: '',
                subject: $request_type
            );
        }
        $response_type_class = implode(
            separator: '\\',
            array: $namespace_parts
        ) . "\\{$response_type}Response";
        if (class_exists($response_type_class)) {
            return new $response_type_class($request);
        } else {
            throw new \InvalidArgumentException("Invalid request type: {$response_type_class}");
        }
    }

    public function __construct()
    {
        self::$request = Request::get();
        // auth middleware
    }
    public function __destruct()
    {
        // do-something middleware
        switch (self::$request->get_type()) {
            case 'Data':
                $response = self::get_response(self::$request, 'JSON');
                break;

            case 'Form':
                $response = self::get_response(self::$request, 'HTML');
                break;

            default:
                $response = self::get_response(self::$request);
                break;
        }
        echo $response;
    }

    public static function add_endpoint(Endpoint $endpoint): void
    {
        try {
            array_push(self::$post_endpoints, $endpoint);
        } catch (\Throwable $e) {
            throw new \Error($e->message);
        }
    }
}
