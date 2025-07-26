<?php

namespace PHP_Library\HTTP\HTTP1Request;

use PHP_Library\HTTP\HTTPMessage\HTTPHeader\AbstractHTTPHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader\HTTPRequestHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPMessage;

/**
 * Represents an HTTP/1.1 request message per RFC 7230 §3.1.1.
 * Includes the method, request URI, version, headers, and body.
 */
class HTTP1Request extends HTTPMessage
{
    /**
     * HTTP method (e.g., 'GET', 'POST', etc.).
     */
    public readonly string $method;

    /**
     * Request URI, as used in the Request-Line.
     */
    public readonly string $request_uri;

    /**
     * HTTP version string (e.g., 'HTTP/1.1').
     */
    public readonly string $http_version;

    /**
     * Constructs an HTTP1Request instance.
     *
     * @param string $method HTTP method
     * @param string $request_uri URI target (default is '/')
     * @param string $http_version HTTP version string (default is 'HTTP/1.1')
     * @param HTTPRequestHeader|null $header Optional request header object
     * @param string $body Optional request body
     */
    public function __construct(string $method, string $request_uri = '/', string $http_version = 'HTTP/1.1', ?HTTPRequestHeader $header = null, string $body = '')
    {
        parent::__construct("$method $request_uri $http_version", $header, $body);
        $this->method = $method;
        $this->request_uri = $request_uri;
        $this->http_version = $http_version;
    }

    /**
     * Returns the request header object.
     *
     * @return HTTPRequestHeader|null
     */
    public function get_header(): ?HTTPRequestHeader
    {
        return $this->header;
    }

    /**
     * Sets or updates a header field value.
     *
     * @param string  $field Header name (case-insensitive)
     * @param string|array|null $value Header value or null if already formatted "Name: Value"
     * @return static
     *
     * @throws \LogicException If the header is immutable
     */
    public function set_header_field(string $field, string|array|null $value = null): static
    {
        $this->header->set($field, $value);
        return $this;
    }

    /**
     * Creates a header object from raw header lines.
     *
     * @param string[] $header_data Raw header lines
     * @return HTTPRequestHeader|null
     */
    public static function create_header(array $header_data): ?AbstractHTTPHeader
    {
        if (! $header_data) {
            return null;
        }

        return new HTTPRequestHeader($header_data);
    }

    /**
     * Provides debug-friendly output including inherited fields.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return array_merge(parent::__debugInfo(), []);
    }

    /**
     * Parses a raw HTTP/1.1 request string into an HTTP1Request object.
     *
     * @param string $http_data Full raw HTTP request text
     * @return static
     */
    public static function from_raw(string $http_data): static
    {
        $request = parent::from_raw($http_data);

        $request_line = explode(' ', $request->start_line);

        // Extract method, URI, and version directly from the start-line
        $request->method = array_shift($request_line);
        $request->request_uri = array_shift($request_line);
        $request->http_version = array_shift($request_line);

        return $request;
    }
}
