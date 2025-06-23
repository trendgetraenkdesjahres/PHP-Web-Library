<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient;

/**
 * Abstract base class for implementing paginated API request strategies.
 */
abstract class AbstractPagination
{
    /** @var int Maximum number of HTTP requests (0 = unlimited). */
    public int $max_requests = 0;

    /** @var int Maximum number of elements to fetch (0 = unlimited). */
    public int $max_elements = 0;

    /** @var int Number of elements per page (0 = no page size limit). */
    public int $page_size = 100;

    /** @var float Delay (in seconds) between consecutive requests. */
    public float $request_delay = 0;

    /** @var int Number of requests performed so far. */
    protected int $request_counter = 1;

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
    abstract protected function browse_forward(array $data): static;

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
    public function prepare_next_page_query(array $data): static
    {
        $this->element_counter = static::count_elements($data);

        if ($this->request_delay > 0) {
            $this->pause_for_delay();
        }

        $this->browse_forward($data);
        $this->request_counter++;

        return $this;
    }

    /**
     * Checks if more data should be fetched.
     */
    public function has_next(): bool
    {
        if ($this->max_requests && $this->request_counter > $this->max_requests) {
            return false;
        }

        if ($this->max_elements && $this->element_counter >= $this->max_elements) {
            return false;
        }

        return !$this->is_on_last_page();
    }

    /**
     * Bulk-set limits and pacing behavior.
     */
    public function set_limits(?int $max_requests = null, ?int $max_elements = null, int $page_size = null, ?float $request_delay = null): static
    {
        foreach ((new \ReflectionMethod(__CLASS__, __FUNCTION__))->getParameters() as $param) {
            $name = $param->getName();
            if ($$name !== null) {
                $this->$name = $$name;
            }
        }

        return $this;
    }

    public function get_status_report(): string {
        return "Received {$this->element_counter} elements on {$this->request_counter} resources";
    }

    /**
     * Counts the number of items in a known data collection key.
     */
    protected static function count_elements(array $data): int
    {
        foreach (['data', 'items', 'results', 'collection'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return count($data[$key]);
            }
        }

        return count($data);
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
    protected function get_flattened_data(array $data, string $prefix = ''): array
    {
        $result = [];

        if (count($data) === 1 && isset($data[0])) {
            $data = $data[0]; // Unwrap outer array with single numeric key
        }

        foreach ($data as $key => $value) {
            $compound_key = $prefix === '' ? $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $result += $this->get_flattened_data($value, $compound_key);
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
}
