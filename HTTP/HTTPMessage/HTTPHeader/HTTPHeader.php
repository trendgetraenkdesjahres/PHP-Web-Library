<?php

namespace PHP_Library\HTTP\HTTPMessage\HTTPHeader;

/**
 * Represents a concrete HTTP header container including
 * RFC-defined general and entity header fields.
 */
class HTTPHeader extends AbstractHTTPHeader
{
    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.1
     */
    public array|null $cache_control = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.2
     */
    public array|string|null $connection = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7232#section-2.3
     */
    public string|null $date = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.2
     */
    public array|string|null $pragma = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.2
     */
    public array|null $trailer = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.2
     */
    public array|null $transfer_encoding = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.3
     */
    public array|null $upgrade = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.4
     */
    public array|null $via = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.1
     */
    public string|array|null $warning = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public array|null $allow = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public array|null $content_encoding = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public array|null $content_language = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public string|null $content_length = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public string|null $content_location = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public string|null $content_md5 = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public string|null $content_range = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.1.5
     */
    public string|null $content_type = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-3.1.2.2
     */
    public string|array|null $expires = null;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.1.2
     */
    public string|null $last_modified = null;

    /**
     * Constructs the HTTPHeader object from header lines, optionally mutable.
     *
     * @param string[] $header_lines Raw header lines.
     * @param bool $is_mutable Defaults to true.
     */
    public function __construct(array $header_lines = [], bool $is_mutable = true)
    {
        $this->is_mutable = $is_mutable;
        parent::__construct($header_lines);
    }

    /**
     * Magic getter: allow HTTP-style access to properties.
     *
     * @param string $name HTTP-Style header field name.
     * @return string|array|null
     */
    public function __get(string $name): string|array|null
    {
        $canonical = static::canonicalize_field_name($name);
        return $this->$canonical ?? null;
    }

    /**
     * Magic setter: allow HTTP-style setting of properties,
     * respecting the mutability state.
     *
     * @param string $name HTTP-Style header field name.
     * @param string|array|null $value
     *
     * @throws \LogicException If instance is immutable.
     */
    public function __set(string $name, string|array|null $value): void
    {
        if (! $this->is_mutable) {
            throw new \LogicException(static::class . ' is immutable.');
        }

        $canonical = static::canonicalize_field_name($name);
        $this->$canonical = $value;
        $this->original_field_names[$canonical] = $name;
    }
}
