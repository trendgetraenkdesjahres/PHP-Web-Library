<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Pagination;

use PHP_Library\Error\Warning;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Payload;

class OffsetPagination extends AbstractPagination
{
    protected static array $offset_field_names = [
        'offset',
        'start',
        'skip',
    ];

    protected ?string $offset_field = null;
    protected int $offset = 0;

    public function __construct(int $offset = 0, ?string $offset_field = null)
    {
        $this->offset = $offset;
        $this->offset_field = $offset_field;
    }

    public function get_current_page_query(): array
    {
        return array_merge(
            $this->get_field_value_array('offset'),
            $this->get_field_value_array('page_size')
        );
    }

    protected function browse_forward(Payload $payload): static
    {
        if (is_null($this->offset_field)) {
            $flat = static::get_flattened_data($payload->to_array());
            $this->offset_field = $this->detect_offset_field_key($flat, static::$offset_field_names);
        }
        
        if (is_null($this->page_size_field)) {
            if(!isset($flat)) {
                $flat = static::get_flattened_data($payload->to_array());
            }
            $this->page_size_field = $this->detect_offset_field_key($flat, static::$page_size_field_names, 'page_size_field');
        }

        $count = $payload->count();
        $new_items = max(0, $count - $this->element_counter);

        $this->offset += $new_items;
        $this->element_counter = $count;

        return $this;
    }

    public function is_on_last_page(): bool
    {
        if ($this->page_size === 0) {
            return true;
        }

        // If page size is set and fewer items were fetched than expected, it's the last page
        if ($this->page_size && ($this->element_counter % $this->page_size) !== 0) {
            return true;
        }

        return false;
    }

    public function reset(): static
    {
        $this->offset = 0;
        $this->element_counter = 0;
        $this->request_counter = 1;
        return $this;
    }

    /**
     * Detect a field from candidates and emit a warning if one is guessed.
     */
    protected function detect_offset_field_key(array $data, array $candidates, string $field_label = 'offset_field'): ?string
    {
        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $data)) {
                Warning::trigger("No \${$field_label} set. Guessing '$candidate'.");
                return $candidate;
            }
        }
        return null;
    }
}
