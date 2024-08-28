<?php

namespace PHP_Library\Types\BaseTypeTraits;

trait PHPArrayFunctions
{
    final public function key_case_to_upper(): static
    {
        $this->value = array_change_key_case($this->value, CASE_UPPER);
        return $this;
    }

    final public function key_case_to_lower(): static
    {
        $this->value = array_change_key_case($this->value, CASE_LOWER);
        return $this;
    }

    final public function chunk(int $length, bool $preserve_keys = false): static
    {
        $this->value = array_chunk($this->value, $length, $preserve_keys);
        return $this;
    }

    final public function get_column(string|int $column_key, string|int|null $index_key = null): static
    {
        return new self(array_column($this->value, $column_key, $index_key));
    }

    final public function reindex(string|int|null $index_key = null): static
    {
        $this->value = array_column($this->value, null, $index_key);
        return $this;
    }

    final static public function combine(array $keys, array $values): static
    {
        return new self(array_combine($keys, $values));
    }

    final public function get_count_values(): static
    {
        return new self(array_count_values($this->array));
    }

    final public function get_value_difference(array ...$array): static
    {
        return new self(array_diff($this->value, ...$array));
    }

    final public function get_associative_difference(array ...$array): static
    {
        return new self(array_diff_assoc($this->value, ...$array));
    }

    final public function get_key_difference(array ...$array): static
    {
        return new self(array_diff_key($this->value, ...$array));
    }

    final public function get_value_intersection(array ...$array): static
    {
        return new self(array_intersect($this->value, ...$array));
    }

    final public function get_associative_intersection(array ...$array): static
    {
        return new self(array_intersect_assoc($this->value, ...$array));
    }

    final public function get_key_intersection(array ...$array): static
    {
        return new self(array_intersect_key($this->value, ...$array));
    }

    final public function get_custom_value_difference(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_udiff($this->value, ...$array));
    }

    final public function get_custom_associative_difference(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_udiff_assoc($this->value, ...$array));
    }

    final public function get_custom_value_intersection(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_uintersect($this->value, ...$array));
    }

    final public function get_custom_associative_intersection(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_uintersect_assoc($this->value, ...$array));
    }

    final static public function fill(int $start_index, int $count, mixed $value): static
    {
        return new self(array_fill($start_index, $count, $value));
    }

    final static public function fill_keys(array $keys, mixed $value): static
    {
        return new self(array_fill_keys($keys, $value));
    }

    final public function filter_values(callable $callback): static
    {
        $this->value = array_filter($this->value, $callback);
        return $this;
    }

    final public function filter_keys(callable $callback): static
    {
        $this->value = array_filter($this->value, $callback, ARRAY_FILTER_USE_KEY);
        return $this;
    }

    final public function filter_keys_and_values(callable $callback): static
    {
        $this->value = array_filter($this->value, $callback, ARRAY_FILTER_USE_BOTH);
        return $this;
    }

    final public function flip(): static
    {
        $this->value = array_flip($this->value);
        return $this;
    }

    final public function is_list(): bool
    {
        return array_is_list($this->value);
    }

    final public function has_key(int|string $key): bool
    {
        return array_key_exists($key, $this->value);
    }

    final public function get_first_key(): int|string|null
    {
        return array_key_first($this->value);
    }

    final public function get_last_key(): int|string|null
    {
        return array_key_last($this->value);
    }

    final public function get_keys(mixed $filter_value, bool $strict = false): array
    {
        return array_keys($this->value, $filter_value, $strict);
    }

    // mutable
    final public function apply_function(callable $function): static
    {
        $this->values = array_map($function, $this->value);
        return $this;
    }

    // immutable
    final public function get_mapped_array(callable $function): static
    {
        return new self(array_map($function, $this->value));
    }

    final public function merge(array ...$array): static
    {
        $this->value = array_merge($this->value, ...$array);
        return $this;
    }

    final public function merge_preserve_numeric_keys(array ...$array): static
    {
        foreach ($array as $array) {
            $this->value = $this->value + $array;
        }
        return $this;
    }

    final public function merge_recursive(array ...$array): static
    {
        $this->value = array_merge_recursive($this->value, ...$array);
        return $this;
    }

    // is only useful as static
    final static public function multisort(array $arrays, mixed $array1_sort_order = SORT_ASC, mixed $array1_sort_flags = SORT_REGULAR): static
    {
        $arrays[] = $array1_sort_order + $array1_sort_flags;
        return new self(array_multisort(...$arrays));
    }


    final public function pad_left(int $length, mixed $value): static
    {
        if ($length < 0) {
            throw new \ValueError("\$length must be positive integer.");
        }
        $this->value = array_pad($this->value, 0 - $length, $value);
        return $this;
    }

    final public function pad_right(int $length, mixed $value): static
    {
        if ($length < 0) {
            throw new \ValueError("\$length must be positive integer.");
        }
        $this->value = array_pad($this->value, $length, $value);
        return $this;
    }

    final public function pop_last_element(): mixed
    {
        return array_pop($this->value);
    }

    final public function get_product(): int|float
    {
        return array_product($this->value);
    }

    final public function push(mixed $value): static
    {
        $this->value[] = $value;
        return $this;
    }

    final public function get_random_key(): int|string
    {
        return array_rand($this->value);
    }

    final public function get_random_keys(int $quantity): static
    {
        return new self(array_rand($this->value, $quantity));
    }

    final public function get_reduced(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->value, $callback, $initial);
    }

    final public function replace_values(array ...$replacements): static
    {
        $this->value = array_replace($this->value, ...$replacements);
        return $this;
    }

    final public function replace_values_recursive(array ...$replacements): static
    {
        $this->value = array_replace_recursive($this->value, ...$replacements);
        return $this;
    }

    final public function reverse(bool $preserve_keys = false): static
    {
        $this->value = array_reverse($this->value, $preserve_keys);
        return $this;
    }

    final public function get_reversed(bool $preserve_keys = false): static
    {
        return new self(array_reverse($this->value, $preserve_keys));
    }

    final public function get_key(mixed $value, bool $strict = false): int|string|false
    {
        return array_search($value, $this->value, $strict);
    }

    final public function shift_off_first_element(): mixed
    {
        return array_shift($this->value);
    }

    final public function get_slice(int $offset, ?int $length = null, bool $preserve_keys = false): static
    {
        return new self(array_slice($this->value, $offset, $length, $preserve_keys));
    }

    final public function get_splice(int $offset, ?int $length = null, array $replacement = []): static
    {
        return new self(array_splice($this->value, $offset, $length, $replacement));
    }

    final public function get_sum(): int|float
    {
        return array_sum($this->value);
    }

    final public function remove_duplicates(int $flags = SORT_STRING): static
    {
        $this->value = array_unique($this->value, $flags);
        return $this;
    }

    final public static function unique(array $array, int $flags = SORT_STRING): static
    {
        return new self(array_unique($array, $flags));
    }

    final public function unshift(mixed ...$values): static
    {
        $this->value = $values + $this->value;
        return $this;
    }

    final public function get_values(): static
    {
        return new self(array_values($this->values));
    }

    final public function walk(callable $function, mixed $args = null): static
    {
        array_walk($this->value, $function, $args);
        return $this;
    }

    final public function walk_recursive(callable $function, mixed $args = null): static
    {
        array_walk_recursive($this->value, $function, $args);
        return $this;
    }

    final public function sort_natural(bool $case_sensetive = false): static
    {
        if ($case_sensetive) {
            $this->value = natsort($this->value);
        } else {
            $this->value = natcasesort($this->value);
        }
        return $this;
    }

    final public function sort_key_ascending(int $flags = SORT_REGULAR): static
    {
        $this->value = ksort($this->value, $flags);
        return $this;
    }

    final public function sort_key_descending(int $flags = SORT_REGULAR): static
    {
        $this->value = krsort($this->value, $flags);
        return $this;
    }

    final public function sort_ascending(bool $maintain_keys = true, int $flags = SORT_REGULAR): static
    {
        if ($maintain_keys) {
            $this->value = asort($this->value, $flags);
        } else {
            $this->value = sort($this->value, $flags);
        }
        return $this;
    }

    final public function sort_descending(bool $maintain_keys = true, int $flags = SORT_REGULAR): static
    {
        if ($maintain_keys) {
            $this->value = arsort($this->value, $flags);
        } else {
            $this->value = rsort($this->value, $flags);
        }
        return $this;
    }

    final static public function compact(array|string $var_name, array|string ...$var_names): static
    {
        return new self(compact($var_name, ...$var_names));
    }

    final public function get_length(): int
    {
        return count($this->value);
    }

    final public function get_size(): int
    {
        return count($this->value);
    }

    final public function has(mixed $value, bool $strict = false): bool
    {
        return in_array($value, $this->value, $strict);
    }

    final public function shuffle(): static
    {
        $this->value = shuffle($this->value);
        return $this;
    }
}
