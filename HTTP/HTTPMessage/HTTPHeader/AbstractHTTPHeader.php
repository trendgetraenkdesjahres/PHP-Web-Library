<?php

namespace PHP_Library\HTTP\HTTPMessage\HTTPHeader;

/**
 * Represents an abstract HTTP header container.
 * Provides field normalization, parsing, and structured representation
 * per RFC 7230–7235 semantics where applicable.
 */
abstract class AbstractHTTPHeader
{
    /**
     * Maps canonical (PHP-style, lowercased) field names to original HTTP-style names.
     * Example: 'content_type' => 'Content-Type'
     *
     * @var array<string, string>
     */
    protected array $original_field_names = [];

    /**
     * Indicates whether the header instance allows mutation.
     *
     * @var bool
     */
    protected bool $is_mutable = true;

    /**
     * List of HTTP header fields that must be treated as singular.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-3.1
     * @var string[]
     */
    private static array $singular_fields = [
        'Content-Type',
        'Content-Length',
        'Content-Encoding',
        'Content-Language',
        'Content-Location',
        'Date',
        'ETag',
        'Host',
        'Last-Modified',
        'Location',
        'Retry-After',
        'Server',
        'User-Agent',
        'Authorization',
        'Referer',
        'Origin'
    ];

    /**
     * Constructs the header from raw lines.
     *
     * For each header field, if a method named "parse_{canonical_field_name}_value" (e.g. parse_set_cookie_value) exists,
     * it will be used to parse that field’s value. Otherwise, singular fields are trimmed,
     * and others parsed generically.
     * 
     * @param string[] $header_lines Array of lines like ['Content-Type: text/html']
     */
    public function __construct(array $header_lines = [])
    {
        $headers = static::parse_header_lines($header_lines);

        foreach ($headers as $field => $value) {
            $canonical = static::canonicalize_field_name($field);
            $this->original_field_names[$canonical] = $field;

            if (method_exists(static::class, "parse_{$field}_value")) {
                $this->$canonical = call_user_func([$this, "parse_{$field}_value"], $value);
                continue;
            }

            $this->$canonical = static::is_singular_field($field)
                ? trim($value)
                : static::parse_header_value($value);
        }
    }

    /**
     * Clones the current header instance into a mutable copy.
     *
     * @return static
     */
    public function clone(): static
    {
        $copy = clone $this;
        $copy->is_mutable = true;

        return $copy;
    }

    /**
     * Merges another header instance or array of raw header lines into this one.
     *
     * @param AbstractHTTPHeader|string[] ...$other
     * @return static
     *
     * @throws \LogicException If the header is marked immutable
     */
    public function merge(AbstractHTTPHeader|array ...$other): static
    {
        if (! $this->is_mutable) {
            throw new \LogicException(static::class . ' is immutable.');
        }

        foreach ($other as $header) {
            $lines = $header instanceof AbstractHTTPHeader
                ? $header->to_lines()
                : $header;

            foreach ($lines as $line) {
                $this->set($line, null); // Parses "Name: Value"
            }
        }

        return $this;
    }

    /**
     * Returns all original HTTP-style header field names.
     *
     * @return string[]
     */
    public function fields(): array
    {
        return array_values($this->original_field_names);
    }

    /**
     * Sets or updates a header field.
     *
     * @param string              $field Header name (HTTP-style or PHP-style)
     * @param string|array|null   $value Header value or null to parse inline "Name: Value"
     * @return static
     *
     * @throws \LogicException If the header is marked immutable
     */
    public function set(string $field, string|array|null $value): static
    {
        if (! $this->is_mutable) {
            throw new \LogicException(static::class . ' is immutable.');
        }

        if ($value === null && str_contains($field, ':')) {
            [$name, $val] = explode(':', $field, 2);
            return $this->set(trim($name), trim($val));
        }

        $canonical = static::canonicalize_field_name($field);
        $this->$canonical = $value;
        $this->original_field_names[$canonical] = $field;

        return $this;
    }

    /**
     * Retrieves the value of a header field if set.
     *
     * @param string $field Header name
     * @return string|array|null
     */
    public function get(string $field): string|array|null
    {
        $canonical = static::canonicalize_field_name($field);
        return $this->$canonical ?? null;
    }

    /**
     * Returns an associative array of all defined header fields and their values.
     *
     * @return array<string, mixed>
     */
    public function to_array(): array
    {
        $all_props = get_object_vars($this);
        $reflection = new \ReflectionObject($this);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPrivate() || $property->isProtected() || is_null($all_props[$property->getName()])) {
                unset($all_props[$property->getName()]);
            }
        }

        return $all_props;
    }

    /**
     * Checks if a specific header field exists.
     *
     * @param string $field Header name
     * @return bool
     */
    public function has(string $field): bool
    {
        return array_key_exists(static::canonicalize_field_name($field), $this->to_array());
    }

    /**
     * Returns the header fields as raw HTTP lines.
     *
     * @return string[] Array of lines like "Content-Type: text/html\r\n"
     */
    public function to_lines(): array
    {
        $lines = [];

        foreach ($this->to_array() as $field => $value) {
            $original = $this->original_field_names[$field] ?? static::normalize_field_name($field);
            $encoded_value = static::format_header_value($value);
            $lines[] = "{$original}: {$encoded_value}";
        }

        return $lines;
    }

    /**
     * Returns the complete header block as a raw HTTP string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode(PHP_EOL,  $this->to_lines());
    }

    public function __debugInfo(): array
    {
        $object_vars = get_object_vars($this); // all public props set on this instance
        $predefined_class_vars = get_class_vars(static::class);

        // Remove predefined public class vars that are still null (unset headers)
        foreach ($predefined_class_vars as $key => $default_value) {
            if (array_key_exists($key, $object_vars) && $object_vars[$key] === null) {
                unset($object_vars[$key]);
            }
        }

        return $object_vars;
    }

    /**
     * Parses raw header lines into a field => value associative array.
     *
     * @param string[] $header_lines
     * @return array<string, string>
     */
    protected static function parse_header_lines(array $header_lines): array
    {
        $headers = [];

        foreach ($header_lines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            [$name, $value] = $parts;
            $headers[trim($name)] = trim($value);
        }

        return $headers;
    }

    /**
     * Parses a structured header value into a token => parameters map.
     *
     * @param string $header_value Raw header value string
     * @return string|array<string, string|float|bool>
     */
    protected static function parse_header_value(string $header_value): string|array
    {
        $field_values = static::split_quoted($header_value, ',');

        if (count($field_values) === 1 && ! str_contains($field_values[0], '=')) {
            return trim($field_values[0]);
        }

        $parsed_values = [];

        foreach ($field_values as $field_value) {
            $parts = static::split_quoted($field_value, ';');
            $item_token = trim(array_shift($parts));
            $parameters = [];

            foreach ($parts as $parameter_part) {
                [$attribute, $value] = array_map('trim', explode('=', $parameter_part, 2) + [1 => true]);
                $parameters[$attribute] = ($attribute === 'q') ? (float) $value : $value;
            }

            $parsed_values[$item_token] = $parameters ?: true;
        }

        return $parsed_values;
    }

    /**
     * Formats a structured header value into a string representation.
     *
     * @param mixed $field_value A string or associative structure
     * @return string
     */
    protected static function format_header_value(mixed $field_value): string
    {
        if (!is_iterable($field_value)) {
            return (string) $field_value;
        }
        $formatted_items = [];

        foreach ($field_value as $item_token => $parameters) {
            if ($parameters === true) {
                $formatted_items[] = $item_token;
                continue;
            }

            $formatted_parameters = [];

            foreach ($parameters as $attribute => $value) {
                $formatted_parameters[] = $value === true
                    ? $attribute
                    : "{$attribute}={$value}";
            }

            $formatted_items[] = "{$item_token}; " . implode('; ', $formatted_parameters);
        }

        return implode(', ', $formatted_items);
    }

    /**
     * Converts an HTTP-style field name to a canonical PHP-style field name.
     *
     * @param string $field HTTP-style field name
     * @return string
     */
    protected static function canonicalize_field_name(string $field): string
    {
        return strtolower(str_replace('-', '_', $field));
    }

    /**
     * Converts a canonical field name to HTTP-style (for output).
     *
     * @param string $field Canonical field name
     * @return string
     */
    protected static function normalize_field_name(string $field): string
    {
        return implode('-', array_map('ucfirst', explode('_', $field)));
    }

    /**
     * Returns the original HTTP-style field name for a canonical key, if available.
     *
     * @param string $field Canonical field name
     * @return string|false
     */
    protected function get_original_field_name(string $field): string|false
    {
        return $this->original_field_names[$field] ?? false;
    }

    /**
     * Determines whether a field must be treated as singular.
     *
     * @param string $field Header field name
     * @return bool
     */
    protected static function is_singular_field(string $field): bool
    {
        $canonical = static::canonicalize_field_name($field);

        foreach (static::$singular_fields as $singular) {
            if (static::canonicalize_field_name($singular) === $canonical) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a string by separator(s) while preserving quoted substrings.
     *
     * @param string $input Input string
     * @param string $separators One or more separator characters
     * @return string[]
     */
    private static function split_quoted(string $input, string $separators = ','): array
    {
        $result = [];
        $length = strlen($input);
        $start = 0;
        $quoted = false;
        $chars = str_split($separators);

        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];
            $prev = $i > 0 ? $input[$i - 1] : '';

            if ($char === '"' && $prev !== '\\') {
                $quoted = ($quoted === $char) ? false : $char;
                continue;
            }

            if (! $quoted && in_array($char, $chars, true)) {
                $segment = substr($input, $start, $i - $start);
                $result[] = trim($segment);
                $start = $i + 1;
            }
        }

        if ($start < $length) {
            $result[] = trim(substr($input, $start));
        }

        return $result;
    }
}
