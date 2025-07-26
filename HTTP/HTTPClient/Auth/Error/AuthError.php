<?php

namespace PHP_Library\HTTP\HTTPClient\Auth\Error;

use PHP_Library\Error\Error;
use PHP_Library\HTTP\HTTP1Request\HTTP1Request;
use PHP_Library\HTTP\HTTPMessage\HTTPMessage;
use PHP_Library\HTTP\HTTPResponse\HTTPResponse;

class AuthError extends Error
{
    protected ?HTTPMessage $request;
    protected ?HTTPMessage $response;
    public function __construct(string $message = "", ?HTTP1Request $request = null, ?HTTPResponse $response = null, int $code = 0, ?\Throwable $previous = null)
    {
        if ($request) {
            $message .= PHP_EOL . static::create_http_message_info($request) . PHP_EOL;
        }
        if ($response) {
            $message .= PHP_EOL . static::create_http_message_info($response) . PHP_EOL;
        }
        parent::__construct($message, $code, $previous);
    }

    protected static function create_http_message_info(HTTPMessage $message): string
    {
        $type = (new \ReflectionClass($message))->getShortName();
        $string = "$type:" . PHP_EOL;
        $string .= (string) $message;
        return $string;
    }
}
