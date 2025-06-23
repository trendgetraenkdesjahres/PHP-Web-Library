<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient;

use PHP_Library\Error\Warning;

class CursorPagination extends AbstractPagination
{
    /**
     * Common request query field names for cursor tokens.
     */
    protected static array $cursor_field_names = [
        'offset',
        'cursor',
        'after',
        'next',
    ];

    /**
     * Common response data keys that hold the next cursor value.
     */
    protected static array $next_cursor_response_keys = [
        'next_cursor',
        'nextPageToken',
        'paging.next',
        'meta.next_cursor',
        'next_href',
    ];

    protected ?string $cursor_field = null;
    protected ?string $next_cursor_response_key = null;

    protected ?string $current_cursor = null;
    protected ?string $next_cursor = null;

    public function __construct(?string $initial_cursor = null, ?string $cursor_field = null)
    {
        $this->current_cursor = $initial_cursor;
        $this->cursor_field = $cursor_field;
    }

    /**
     * Get query parameters to request the current page.
     */
    public function get_current_page_query(): array
    {
        $query = [];

        if ($this->cursor_field && $this->current_cursor) {
            $query[$this->cursor_field] = $this->current_cursor;
        }

        return array_merge($query, $this->get_field_value_array('page_size'));
    }

    /**
     * Use the response data to move forward in pagination.
     */
    protected function browse_forward(array $data): static
    {
        $flat = $this->get_flattened_data($data);

        if (!$this->cursor_field) {
            $this->cursor_field = $this->detect_cursor_field_key($flat, static::$cursor_field_names, 'cursor_field');
        }

        if (!$this->next_cursor_response_key) {
            $this->next_cursor_response_key = $this->detect_cursor_field_key($flat, static::$next_cursor_response_keys, 'next_cursor_response_key');
        }

        $next_cursor = $this->detect_cursor_value($flat, static::$next_cursor_response_keys);

        if ($next_cursor && filter_var($next_cursor, FILTER_VALIDATE_URL)) {
            $extracted = $this->extract_cursor_from_url($next_cursor);
            if ($extracted !== null) {
                $next_cursor = $extracted;
            }
        }

        $this->next_cursor = $next_cursor;
        $this->current_cursor = $this->next_cursor;

        return $this;
    }

    /**
     * Determines whether there is another page.
     */
    public function is_on_last_page(): bool
    {
        return empty($this->next_cursor);
    }

    /**
     * Reset internal state.
     */
    public function reset(): static
    {
        $this->current_cursor = null;
        $this->next_cursor = null;
        $this->request_counter = 1;
        $this->element_counter = 0;
        return $this;
    }

    /**
     * Find the first matching field in data.
     */
    protected function detect_cursor_field_key(array $data, array $candidates, string $field_name): ?string
    {
        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $data)) {
                Warning::trigger("No \$this->{$field_name} set. guessing '$candidate'.");
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Detect the value for a known or guessed cursor key.
     */
    protected function detect_cursor_value(array $data, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $data)) {
                $value = $data[$candidate];
                return is_scalar($value) ? (string) $value : null;
            }
        }
        return null;
    }

    /**
     * Extract cursor token from URL string.
     */
    protected function extract_cursor_from_url(string $url): ?string
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if (!$query) {
            return null;
        }

        parse_str($query, $queryParams);

        if (!$this->cursor_field) {
            $this->cursor_field = $this->detect_cursor_field_key($queryParams, static::$cursor_field_names, 'cursor_field');
        }

        $field = $this->cursor_field;
        return $field && isset($queryParams[$field]) ? (string)$queryParams[$field] : null;
    }
}
