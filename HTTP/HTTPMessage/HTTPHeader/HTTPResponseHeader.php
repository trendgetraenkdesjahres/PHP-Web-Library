<?php

namespace PHP_Library\HTTP\HTTPMessage\HTTPHeader;

/**
 * Represents HTTP response headers, extending HTTPHeader with
 * response-specific RFC-defined fields.
 */
#[\AllowDynamicProperties]
class HTTPResponseHeader extends HTTPHeader
{
    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.2
     */
    public string|null $age = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.3
     */
    public string|null $etag = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7232#section-2.1
     */
    public string|null $location = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.4
     */
    public array|null $proxy_authenticate = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7235#section-4.3
     */
    public string|null $retry_after = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.6
     */
    public string|null $server = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7235#section-4.2
     */
    public array|null $set_cookie = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.7
     */
    public null|string|array $vary = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.8
     */
    public array|null $www_authenticate = null;

    /**
     * Constructs the immutable HTTPResponseHeader from header lines,.
     *
     * @param string[] $header_lines Raw header lines.
     */
    public function __construct(array $header_lines = [])
    {
        parent::__construct($header_lines);
        $this->is_mutable = false;
    }

    /**
     * Parses one or more Set‑Cookie header lines.
     * Automatically invoked by AbstractHTTPHeader::__construct()
     * because the canonical field name is "set_cookie".
     *
     * @param string $raw Raw header value(s) – may contain embedded CRLFs.
     * @return string[]  Array of cookie strings.
     */
    protected function parse_set_cookie_value(string $raw): array
    {
        // Accept either:
        //   – one cookie per line  (usual case: duplicate Set‑Cookie fields)
        //   – several cookies concatenated with \r\n for convenience
        $cookieLines = preg_split('/\r?\n/', $raw);
        $cookies = [];

        foreach ($cookieLines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // In case the line still contains the field name, strip it.
            if (stripos($line, 'set-cookie:') === 0) {
                $line = trim(substr($line, 11));
            }
            $cookies[] = $line;
        }

        return $cookies;
    }

    /**
     * Extend parent::set() so that each call to Set‑Cookie APPENDS,
     * never overwrites the existing cookie list.
     */
    public function set(string $field, string|array|null $value): static
    {
        $canonical = static::canonicalize_field_name($field);

        if ($canonical !== 'set_cookie') {
            // Delegate all other headers to the parent implementation
            return parent::set($field, $value);
        }

        if (! $this->is_mutable) {
            throw new \LogicException(static::class . ' is immutable.');
        }

        if ($value === null) {
            // Null means “erase” the cookies
            $this->set_cookie = null;
            $this->original_field_names[$canonical] = $field;
            return $this;
        }

        // Normalise to array of strings
        $incoming = is_string($value) ? [$value] : $value;

        if (! is_array($incoming)) {
            throw new \InvalidArgumentException('Set‑Cookie value must be string, array, or null.');
        }

        // Append, keeping existing cookies
        $this->set_cookie = array_merge($this->set_cookie ?? [], array_values($incoming));
        $this->original_field_names[$canonical] = $field;

        return $this;
    }
}
