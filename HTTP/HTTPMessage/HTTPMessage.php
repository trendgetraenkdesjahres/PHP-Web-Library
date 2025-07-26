<?php

namespace PHP_Library\HTTP\HTTPMessage;

use PHP_Library\HTTP\HTTPMessage\HTTPHeader\AbstractHTTPHeader;
use ReflectionClass;

/**
 * Represents a generic HTTP message as defined in RFC 7230.
 * Encapsulates the start-line, headers, and message body.
 */
abstract class HTTPMessage
{
    /**
     * The start-line of the HTTP message (Request-Line or Status-Line).
     */
    public string $start_line;

    /**
     * The header block, represented as an AbstractHTTPHeader.
     */
    protected ?AbstractHTTPHeader $header;

    /**
     * The raw message body as received or to be sent.
     */
    public string $raw_body;

    /**
     * Factory method to create a header object from raw header lines.
     *
     * @param string[] $header_data Array of header lines like ['Host: example.com']
     * @return AbstractHTTPHeader|null
     */
    abstract public static function create_header(array $header_data): null|AbstractHTTPHeader;

    /**
     * Returns the header object, if available.
     *
     * @return AbstractHTTPHeader|null
     */
    abstract public function get_header(): null|AbstractHTTPHeader;

    /**
     * Constructs the HTTPMessage instance.
     *
     * @param string $start_line The start-line of the HTTP message
     * @param AbstractHTTPHeader|null $header Header object or null
     * @param string $body Optional message body (defaults to empty string)
     */
    public function __construct(string $start_line, ?AbstractHTTPHeader $header, string $body = '')
    {
        $this->start_line = $start_line;
        $this->header = $header;
        $this->raw_body = $body;
    }

    /**
     * Provides a debug-friendly representation of the message.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        $info = [];

        if ($this->start_line) {
            $info['start_line'] = $this->start_line;
        }

        if ($this->header) {
            $info['header'] = $this->header->to_array();
        }

        if ($this->raw_body) {
            $info['raw_body'] = $this->raw_body;
        }

        return $info;
    }

    /**
     * Returns the full HTTP message as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->start_line . PHP_EOL
            . $this->get_header() . PHP_EOL . PHP_EOL
            . $this->raw_body;
    }

    /**
     * Creates a new HTTPMessage instance from a raw HTTP string.
     *
     * @param string $http_data Raw HTTP message string
     * @return static
     *
     * @throws \Error If the start-line cannot be parsed
     */
    public static function from_raw(string $http_data): static
    {
        $http_data_parts = preg_split("/\R{2,}/", $http_data);

        $body = $http_data_parts[1] ?? null;

        $header_data = explode(PHP_EOL, $http_data_parts[0]);

        // Remove any empty lines from headers
        $header_data = array_filter($header_data, fn($value) => (bool) $value);

        if (! $start_line = array_shift($header_data)) {
            throw new \Error('Error parsing http data');
        }

        $header = static::create_header($header_data);

        // Create instance without constructor to manually set internals
        $reflection = new ReflectionClass(static::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('start_line')->setValue($instance, $start_line);
        $reflection->getProperty('header')->setValue($instance, $header);
        $reflection->getProperty('raw_body')->setValue($instance, $body);

        return $instance;
    }

    /**
     * Retrieves a specific header field value by its PHP-style name.
     *
     * @param string $field_name Canonicalized header field name (e.g., 'content_type')
     * @return string|array|int|null The field value or null if unset
     */
    public function get_header_field(string $field_name): array|string|int|null
    {
        return $this->header->$field_name ?? null;
    }
}
