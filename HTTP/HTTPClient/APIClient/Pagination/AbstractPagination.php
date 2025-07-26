<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Pagination;

use PHP_Library\Error\Warning;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Payload;

/**
 * Abstract base class for implementing paginated API request strategies.
 */
abstract class AbstractPagination
{
    /** @var int Maximum number of HTTP requests (0 = unlimited). */
    protected int $max_requests = 0;

    /** @var int Maximum number of elements to fetch (0 = unlimited). */
    protected int $max_elements = 0;

    /** @var int Number of elements per page (0 = no page size limit). */
    protected int $page_size = 100;

    /** @var float Delay (in seconds) between consecutive requests. */
    protected float $request_delay = 0;

    /** @var int Number of requests performed so far. */
    protected int $request_counter = 0;

    /** @var int Number of elements fetched so far. */
    protected int $element_counter = 0;

    /** @var string[] Common key names for page size parameter. */
    protected static array $page_size_field_names = [
        'page_size',
        'limit',
        'num',
        'count',
    ];

    /** @var string|null Name of the field used to set page size in query string. */
    protected ?string $page_size_field = null;

    /**
     * Returns query parameters for the current page request.
     */
    abstract public function get_current_page_query(): array;

    /**
     * Advances pagination state using data from last page.
     */
    abstract protected function browse_forward(Payload $payload): static;

    /**
     * Checks if pagination has reached its end.
     */
    abstract public function is_on_last_page(): bool;

    /**
     * Resets internal counters and state.
     */
    abstract public function reset(): static;

    /**
     * Called after each request to advance pagination state.
     */
    public function prepare_next_page_query(Payload $payload): static
    {

        if ($this->request_delay > 0) {
            $this->pause_for_delay();
        }


        $this->browse_forward($payload);
        $this->request_counter++;
        return $this;
    }

    /**
     * Checks if more data should be fetched.
     */
    public function has_next(int $current_elements = 0): bool
    {
        if ($this->max_requests && $this->request_counter > $this->max_requests) {
            return false;
        }

        if ($this->max_elements && $current_elements >= $this->max_elements) {
            return false;
        }

        return !$this->is_on_last_page();
    }

    /**
     * Bulk-set limits and pacing behavior.
     */
    public function set_limits(?int $max_requests = null, ?int $max_elements = null, ?int $page_size = null, ?float $request_delay = null): static
    {
        if (is_int($max_requests)) {
            $this->max_requests = $max_requests;
        }
        if (is_int($max_elements)) {
            $this->max_elements = $max_elements;
        }
        if (is_int($page_size)) {
            $this->page_size = $page_size;
        }
        if (is_int($request_delay)) {
            $this->request_delay = $request_delay;
        }
        return $this;
    }

    public function count_requests(): int
    {
        return $this->request_counter;
    }

    /**
     * Pause based on request delay value.
     */
    protected function pause_for_delay(): void
    {
        $whole = (int)$this->request_delay;
        $fraction = (int)(fmod($this->request_delay, 1) * 1_000_000);

        if ($whole > 0) {
            sleep($whole);
        }

        if ($fraction > 0) {
            usleep($fraction);
        }
    }

    /**
     * Flattens a nested associative array using dot-notation.
     */
    protected static function  get_flattened_data(array $data, string $prefix = ''): array
    {
        $result = [];

        if (count($data) === 1 && isset($data[0])) {
            $data = $data[0]; // Unwrap outer array with single numeric key
        }

        foreach ($data as $key => $value) {
            $compound_key = $prefix === '' ? $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $result += static::get_flattened_data($value, $compound_key);
            } else {
                $result[$compound_key] = $value;
            }
        }

        return $result;
    }

    /**
     * Builds an array like ['limit' => 100] from the matching field name.
     */
    protected function get_field_value_array(string $field): array
    {
        $field_name = "{$field}_field";

        if ($this->$field !== 0) {
            if (is_null($this->$field_name)) {
                $guess = static::$page_size_field_names[0];
                \PHP_Library\Error\Warning::trigger("No \$this->{$field_name} set. guessing '$guess'.");
                $this->$field_name = $guess;
            }

            return [$this->$field_name => $this->$field];
        }

        return [];
    }


    /**
     * Factory method to detect and instantiate the appropriate pagination strategy
     * based on the structure of the first API response.
     * 
     * This method flattens the response data and checks for known pagination
     * indicators to determine whether to use CursorPagination, OffsetPagination,
     * or (optionally) PageNumberPagination.
     * 
     * Detection order is:
     *  1. Cursor-based pagination (via keys like 'next_cursor', 'paging.next', etc.)
     *  2. Offset-based pagination (via keys like 'offset', 'start', etc.)
     *  3. Page-number-based pagination (optional, if class is available)
     * 
     * Each match triggers a PHP_Library\Error\Warning for visibility.
     * 
     * @param array $payload_meta The API response meta array
     * 
     * @return false|AbstractPagination An instance of the detected pagination strategy.
     */
    public static function create_from_first_responses_meta(array $payload_meta): AbstractPagination|false
    {
        $meta_keys = array_keys($payload_meta);

        // 1. Cursor-based detection
        foreach (CursorPagination::$next_cursor_response_keys as $key) {
            if (in_array($key, $meta_keys)) {
                Warning::trigger("Detected '$key' in payload. Using CursorPagination.");
                return new CursorPagination();
            }
        }

        // 2. Offset-based detection
        foreach (OffsetPagination::$offset_field_names as $key) {
            if (in_array($key, $meta_keys)) {
                Warning::trigger("Detected '$key' in payload. Using OffsetPagination.");
                return new OffsetPagination();
            }
        }

        // 3. Page-number based detection (if available)
        /*     if (class_exists(PageNumberPagination::class)) {
        foreach (PageNumberPagination::$page_field_names as $key) {
            if (in_array($key, $meta_keys)) {
                \PHP_Library\Error\Warning::trigger("Detected '$key' in payload. Using PageNumberPagination.");
                return new PageNumberPagination();
            }
        }
    } */

        Warning::trigger('Unable to detect pagination strategy from the payload.');
        return false;
    }
}
