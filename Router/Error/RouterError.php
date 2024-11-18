<?php

namespace PHP_Library\Router\Error;

use PHP_Library\Router\EndpointTypes\Callback;
use PHP_Library\Router\Router;
use PHP_Library\Superglobals\Get;
use Throwable;

class RouterError extends \PHP_Library\Error\Error
{
    public function __construct(string $message = "", int $http_code = 500, ?Throwable $previous = null)
    {
        if (!isset(Router::$current_endpoint)) {
            Router::$current_endpoint = new Callback(Get::get_path(), function () use ($message) {
                return "<pre>{$message}</pre>";
            });
        }
        Router::$current_endpoint->add_http_header('x-error', $message)->status_code = $http_code;
        parent::__construct($message, $http_code, $previous);
    }
}
