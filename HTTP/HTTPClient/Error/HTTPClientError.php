<?php

namespace PHP_Library\HTTP\HTTPClient\Error;

use PHP_Library\HTTP\HTTPClient\HTTP1Client;

class HTTPClientError extends \PHP_Library\Error\Error
{
    protected static string $emoji = '🌐';

    public function __construct(string $message = "", ?HTTP1Client $client = null, int $code = 0, ?\Throwable $previous = null)
    {
        $trace = debug_backtrace(1, 2);
        $client_frame = end($trace);
        if (
            is_null($client)
            && $client_frame['object']
            && is_a($client_frame['object'], HTTP1Client::class, true)
        ) {
            $client = $client_frame['object'];
        }
        if (!$code) {
            $code = isset($client->response) ? $client->response->status_code : 0;
        }
        $message = isset($client->response) ? $client->response->reason_phrase . ($message ? " / $message" : "") . ($client->get_current_request_url() ?  " " . $client->get_current_request_url() : "") : $message;
        parent::__construct($message, $code, $previous);
    }
}
