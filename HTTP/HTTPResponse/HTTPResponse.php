<?php

namespace PHP_Library\HTTP\HTTPResponse;

use PHP_Library\HTTP\HTTPMessage\HTTPHeader\AbstractHTTPHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader\HTTPResponseHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPMessage;

/**
 * Represents an HTTP response message per RFC 7230 §3.1.2.
 * Contains the status line, headers, and body of an HTTP response.
 */
class HTTPResponse extends HTTPMessage
{
    /**
     * HTTP protocol version (e.g., 'HTTP/1.1').
     */
    public readonly string $http_version;

    /**
     * Numeric HTTP status code (e.g., 200, 404).
     */
    public readonly int $status_code;

    /**
     * Reason phrase associated with the status code (e.g., 'OK', 'Not Found').
     */
    public readonly string $reason_phrase;

    /**
     * Constructs an HTTPResponse instance.
     *
     * @param string                   $http_version   Protocol version string
     * @param int                      $status_code    HTTP status code
     * @param string                   $reason_phrase  Status reason phrase
     * @param HTTPResponseHeader|null $header         Optional header object
     * @param string|null             $body           Optional body string
     */
    public function __construct(
        string $http_version,
        int $status_code,
        string $reason_phrase,
        ?HTTPResponseHeader $header,
        ?string $body
    ) {
        parent::__construct("$http_version $status_code $reason_phrase", $header, $body ?? '');
        $this->http_version = $http_version;
        $this->status_code = $status_code;
        $this->reason_phrase = $reason_phrase;
    }

    /**
     * Returns the response header object.
     *
     * @return HTTPResponseHeader|null
     */
    public function get_header(): ?HTTPResponseHeader
    {
        return $this->header;
    }

    /**
     * Factory method for creating a response header from raw header lines.
     *
     * @param string[] $header_data Array of header lines
     * @return HTTPResponseHeader|null
     */
    public static function create_header(array $header_data): ?AbstractHTTPHeader
    {
        if (! $header_data) {
            return null;
        }

        return new HTTPResponseHeader($header_data);
    }

    /**
     * Parses a raw HTTP response string into an HTTPResponse instance.
     *
     * @param string $http_data Raw HTTP response text
     * @return static
     */
    public static function from_raw(string $http_data): static
    {
        $response = parent::from_raw($http_data);

        $status_line = explode(' ', $response->start_line);
        $response->http_version = array_shift($status_line);
        $response->status_code = (int) array_shift($status_line);
        $response->reason_phrase = trim(implode(' ', $status_line));

        return $response;
    }

    /**
     * Applies a regular expression to the response body and returns all matches.
     *
     * @param string $regex_pattern A valid regex pattern (e.g., '/<title>(.*?)<\/title>/')
     * @return array Array of matches (empty if none)
     */
    public function find(string $regex_pattern): array
    {
        if (! $this->raw_body) {
            return [];
        }

        $results = [];
        preg_match_all($regex_pattern, $this->raw_body, $results);

        return $results;
    }
}
