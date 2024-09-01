<?php

namespace PHP_Library\Types\BaseTypeTraits;

trait PHPArrayFunctions
{
    /**
     * Converts all array keys to uppercase.
     *
     * Alias of `array_change_key_case` with `CASE_UPPER`.
     *
     * @return static Returns the instance with updated keys.
     */
    final public function key_case_to_upper(): static
    {
        $this->value = array_change_key_case($this->value, CASE_UPPER);
        return $this;
    }

    /**
     * Converts all array keys to lowercase.
     *
     * Alias of `array_change_key_case` with `CASE_LOWER`.
     *
     * @return static Returns the instance with updated keys.
     */
    final public function key_case_to_lower(): static
    {
        $this->value = array_change_key_case($this->value, CASE_LOWER);
        return $this;
    }

    /**
     * Changes the case of array keys in a new array.
     *
     * Static alias of `array_change_key_case`.
     *
     * @param array $array The input array.
     * @param int $case The case to convert keys to, `CASE_UPPER` or `CASE_LOWER`.
     * @return static Returns a new instance with the case-changed keys.
     */
    final static public function change_key_case(array $array, int $case = CASE_LOWER): static
    {
        return new self(array_change_key_case($array, $case));
    }

    /**
     * Splits an array into chunks.
     *
     * Alias of `array_chunk`.
     *
     * @param int $length The number of elements in each chunk.
     * @param bool $preserve_keys Whether to preserve the original array keys.
     * @return static Returns the instance with the array split into chunks.
     */
    final public function chunk(int $length, bool $preserve_keys = false): static
    {
        $this->value = array_chunk($this->value, $length, $preserve_keys);
        return $this;
    }

    /**
     * Splits an array into chunks and returns a new instance.
     *
     * Static alias of `array_chunk`.
     *
     * @param array $array The input array.
     * @param int $length The number of elements in each chunk.
     * @param bool $preserve_keys Whether to preserve the original array keys.
     * @return static Returns a new instance with the array split into chunks.
     */
    final static public function chunk_array(array $array, int $length, bool $preserve_keys = false): static
    {
        return new self(array_chunk($array, $length, $preserve_keys));
    }


    /**
     * Retrieves values from a single column in the array.
     *
     * Alias of `array_column`.
     *
     * @param string|int $column_key The column to retrieve.
     * @param string|int|null $index_key The column to use as the index for the returned array.
     * @return static Returns a new instance with the specified column values.
     */
    final public function get_column(string|int $column_key, string|int|null $index_key = null): static
    {
        return new self(array_column($this->value, $column_key, $index_key));
    }

    /**
     * Retrieves values from a single column in a new array.
     *
     * Static alias of `array_column`.
     *
     * @param array $array The input array.
     * @param string|int|null $column_key The column to retrieve.
     * @param string|int|null $index_key The column to use as the index for the returned array.
     * @return static Returns a new instance with the specified column values.
     */
    final static public function column(array $array, string|int|null $column_key, string|int|null $index_key = null): static
    {
        return new self(array_column($array, $column_key, $index_key));
    }

    /**
     * Reindexes an array using a specific column.
     *
     * Alias of `array_column` with `null` for the column value.
     *
     * @param string|int|null $index_key The column to use as the index for the returned array.
     * @return static Returns the instance with the array reindexed.
     */
    final public function reindex(string|int|null $index_key = null): static
    {
        $this->value = array_column($this->value, null, $index_key);
        return $this;
    }

    /**
     * Counts all the values of the array.
     *
     * Alias of `array_count_values`.
     *
     * @return static Returns a new instance with the counts of each value.
     */
    final public function get_values_count(): static
    {
        return new self(array_count_values($this->value));
    }

    /**
     * Counts all the values of a new array.
     *
     * Static alias of `array_count_values`.
     *
     * @param array $array The input array.
     * @return static Returns a new instance with the counts of each value.
     */
    final static public function count_values(array $array): static
    {
        return new self(array_count_values($array));
    }


    /**
     * Computes the difference between this array and other arrays.
     *
     * Alias of `array_diff`.
     *
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the values that are present in the first array but not in the others.
     */
    final public function get_value_difference(array ...$array): static
    {
        return new self(array_diff($this->value, ...$array));
    }


    /**
     * Computes the difference between arrays.
     *
     * Static alias of `array_diff`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the values that are present in the first array but not in the others.
     */
    final static public function diff(array $array1, array ...$arrays): static
    {
        return new self(array_diff($array1, ...$arrays));
    }

    /**
     * Computes the difference between this array and other arrays with key association.
     *
     * Alias of `array_diff_assoc`.
     *
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the values and keys that are present in the first array but not in the others.
     */
    final public function get_associative_difference(array ...$array): static
    {
        return new self(array_diff_assoc($this->value, ...$array));
    }


    /**
     * Computes the difference between arrays with key association.
     *
     * Static alias of `array_diff_assoc`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the values and keys that are present in the first array but not in the others.
     */
    final static public function diff_assoc(array $array1, array ...$arrays): static
    {
        return new self(array_diff_assoc($array1, ...$arrays));
    }

    /**
     * Computes the difference between this array and other arrays using keys.
     *
     * Alias of `array_diff_key`.
     *
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the keys that are present in the first array but not in the others.
     */
    final public function get_key_difference(array ...$array): static
    {
        return new self(array_diff_key($this->value, ...$array));
    }

    /**
     * Computes the difference between arrays using keys.
     *
     * Static alias of `array_diff_key`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the keys that are present in the first array but not in the others.
     */
    final static public function diff_key(array $array1, array ...$arrays): static
    {
        return new self(array_diff_key($array1, ...$arrays));
    }

    /**
     * Computes the intersection of this array and other arrays with values.
     *
     * Alias of `array_intersect`.
     *
     * @param array ...$array Arrays to intersect.
     * @return static Returns a new instance with the values that are present in all arrays.
     */
    final public function get_value_intersection(array ...$array): static
    {
        return new self(array_intersect($this->value, ...$array));
    }

    /**
     * Computes the intersection of arrays with values.
     *
     * Static alias of `array_intersect`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to intersect.
     * @return static Returns a new instance with the values that are present in all arrays.
     */
    final static public function intersect(array $array1, array ...$arrays): static
    {
        return new self(array_intersect($array1, ...$arrays));
    }

    /**
     * Computes the intersection of this array and other arrays with key association.
     *
     * Alias of `array_intersect_assoc`.
     *
     * @param array ...$array Arrays to intersect.
     * @return static Returns a new instance with the values and keys that are present in all arrays.
     */
    final public function get_associative_intersection(array ...$array): static
    {
        return new self(array_intersect_assoc($this->value, ...$array));
    }

    /**
     * Computes the intersection of arrays with key association.
     *
     * Static alias of `array_intersect_assoc`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to intersect.
     * @return static Returns a new instance with the values and keys that are present in all arrays.
     */
    final static public function intersect_assoc(array $array1, array ...$arrays): static
    {
        return new self(array_intersect_assoc($array1, ...$arrays));
    }


    /**
     * Computes the intersection of this array and other arrays using keys.
     *
     * Alias of `array_intersect_key`.
     *
     * @param array ...$array Arrays to intersect.
     * @return static Returns a new instance with the keys that are present in all arrays.
     */
    final public function get_key_intersection(array ...$array): static
    {
        return new self(array_intersect_key($this->value, ...$array));
    }

    /**
     * Computes the intersection of arrays using keys.
     *
     * Static alias of `array_intersect_key`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to intersect.
     * @return static Returns a new instance with the keys that are present in all arrays.
     */
    final static public function intersect_key(array $array1, array ...$arrays): static
    {
        return new self(array_intersect_key($array1, ...$arrays));
    }

    /**
     * Computes the difference of this array and other arrays using a user-defined comparison function.
     *
     * Alias of `array_udiff`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the array difference.
     */
    final public function get_custom_value_difference(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_udiff($this->value, ...$array));
    }

    /**
     * Computes the difference of arrays using a user-defined comparison function.
     *
     * Static alias of `array_udiff`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the array difference.
     */
    final static public function udiff(callable $value_compare_function, array $array1, array ...$arrays): static
    {
        $arrays[] = $value_compare_function;
        return new self(array_udiff($array1, ...$arrays));
    }

    /**
     * Computes the difference of this array and other arrays with additional indexes compared using a user-defined comparison function.
     *
     * Alias of `array_udiff_assoc`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the associative array difference.
     */
    final public function get_custom_associative_difference(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_udiff_assoc($this->value, ...$array));
    }

    /**
     * Computes the difference of arrays with additional indexes using a user-defined comparison function.
     *
     * Static alias of `array_udiff_assoc`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the associative array difference.
     */
    final static public function udiff_assoc(callable $value_compare_function, array $array1, array ...$arrays): static
    {
        $arrays[] = $value_compare_function;
        return new self(array_udiff_assoc($array1, ...$arrays));
    }

    /**
     * Computes the intersection of this array and other arrays using a user-defined comparison function.
     *
     * Alias of `array_uintersect`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the array intersection.
     */
    final public function get_custom_value_intersection(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_uintersect($this->value, ...$array));
    }

    /**
     * Computes the intersection of arrays using a user-defined comparison function.
     *
     * Static alias of `array_uintersect`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the array intersection.
     */
    final static public function uintersect(callable $value_compare_function, array $array1, array ...$arrays): static
    {
        $arrays[] = $value_compare_function;
        return new self(array_uintersect($array1, ...$arrays));
    }

    /**
     * Computes the intersection of this array and other arrays with additional indexes using a user-defined comparison function.
     *
     * Alias of `array_uintersect_assoc`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array ...$array Arrays to compare against.
     * @return static Returns a new instance with the associative array intersection.
     */
    final public function get_custom_associative_intersection(callable $value_compare_function, array ...$array): static
    {
        $array[] = $value_compare_function;
        return new self(array_uintersect_assoc($this->value, ...$array));
    }

    /**
     * Computes the intersection of arrays with additional indexes using a user-defined comparison function.
     *
     * Static alias of `array_uintersect_assoc`.
     *
     * @param callable $value_compare_function The comparison function.
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to compare against.
     * @return static Returns a new instance with the associative array intersection.
     */
    final static public function uintersect_assoc(callable $value_compare_function, array $array1, array ...$arrays): static
    {
        $arrays[] = $value_compare_function;
        return new self(array_uintersect_assoc($array1, ...$arrays));
    }

    /**
     * Filters values of this array using a callback function.
     *
     * Alias of `array_filter`.
     *
     * @param callable $callback The callback function.
     * @return static Returns the instance after filtering values.
     */
    final public function filter_values(callable $callback): static
    {
        $this->value = array_filter($this->value, $callback);
        return $this;
    }

    /**
     * Filters keys of this array using a callback function.
     *
     * Alias of `array_filter` with mode `ARRAY_FILTER_USE_KEY`.
     *
     * @param callable $callback The callback function.
     * @return static Returns the instance after filtering keys.
     */
    final public function filter_keys(callable $callback): static
    {
        $this->value = array_filter($this->value, $callback, ARRAY_FILTER_USE_KEY);
        return $this;
    }

    /**
     * Filters keys and values of this array using a callback function.
     *
     * Alias of `array_filter` with mode `ARRAY_FILTER_USE_BOTH`.
     *
     * @param callable $callback The callback function.
     * @return static Returns the instance after filtering keys and values.
     */
    final public function filter_keys_and_values(callable $callback): static
    {
        $this->value = array_filter($this->value, $callback, ARRAY_FILTER_USE_BOTH);
        return $this;
    }

    /**
     * Filters the array using a callback function and returns a new instance.
     *
     * Static alias of `array_filter`.
     *
     * @param array $array The input array.
     * @param callable $callback The callback function.
     * @param int $mode Optional mode to specify which parameters are sent to the callback.
     * @return static Returns a new instance after filtering the array.
     */
    final static public function filter(array $array, callable $callback, int $mode = 0): static
    {
        return new self(array_filter($array, $callback, $mode));
    }

    /**
     * Flips the keys and values of this array.
     *
     * Alias of `array_flip`.
     *
     * @return static Returns the instance after flipping the array.
     */
    final public function flip(): static
    {
        $this->value = array_flip($this->value);
        return $this;
    }

    /**
     * Flips the keys and values of an array and returns a new instance.
     *
     * Static alias of `array_flip`.
     *
     * @param array $array The input array.
     * @return static Returns a new instance after flipping the array.
     */
    final static public function flip_array(array $array): static
    {
        return new self(array_flip($array));
    }

    /**
     * Merges one or more arrays into this array.
     *
     * Alias of `array_merge`.
     *
     * @param array ...$array Arrays to merge.
     * @return static Returns the instance after merging arrays.
     */
    final public function merge(array ...$array): static
    {
        $this->value = array_merge($this->value, ...$array);
        return $this;
    }

    /**
     * Merges arrays into this array while preserving numeric keys.
     *
     * Alias of `array_merge`.
     *
     * @param array ...$array Arrays to merge.
     * @return static Returns the instance after merging arrays.
     */
    final public function merge_preserve_numeric_keys(array ...$array): static
    {
        foreach ($array as $array) {
            $this->value = $this->value + $array;
        }
        return $this;
    }

    /**
     * Merges one or more arrays into a new instance.
     *
     * Static alias of `array_merge`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to merge.
     * @return static Returns a new instance after merging arrays.
     */
    final static public function merge_arrays(array $array1, array ...$arrays): static
    {
        return new self(array_merge($array1, ...$arrays));
    }

    /**
     * Merges one or more arrays into this array recursively.
     *
     * Alias of `array_merge_recursive`.
     *
     * @param array ...$array Arrays to merge.
     * @return static Returns the instance after merging arrays.
     */
    final public function merge_recursive(array ...$array): static
    {
        $this->value = array_merge_recursive($this->value, ...$array);
        return $this;
    }

    /**
     * Merges one or more arrays recursively into a new instance.
     *
     * Static alias of `array_merge_recursive`.
     *
     * @param array $array1 The first array.
     * @param array ...$arrays Arrays to merge.
     * @return static Returns a new instance after merging arrays.
     */
    final static public function merge_arrays_recursive(array $array1, array ...$arrays): static
    {
        return new self(array_merge_recursive($array1, ...$arrays));
    }

    /**
     * Replaces values in this array with values from other arrays.
     *
     * Alias of `array_replace`.
     *
     * @param array ...$replacements Arrays containing replacements.
     * @return static Returns the instance after replacing values.
     */
    final public function replace_values(array ...$replacements): static
    {
        $this->value = array_replace($this->value, ...$replacements);
        return $this;
    }

    /**
     * Replaces values in an array with values from other arrays.
     *
     * Static alias of `array_replace`.
     *
     * @param array $array1 The array to be replaced.
     * @param array ...$replacements Arrays containing replacements.
     * @return static Returns a new instance after replacing values.
     */
    final static public function replace(array $array1, array ...$replacements): static
    {
        return new self(array_replace($array1, ...$replacements));
    }

    /**
     * Replaces values in this array recursively with values from other arrays.
     *
     * Alias of `array_replace_recursive`.
     *
     * @param array ...$replacements Arrays containing replacements.
     * @return static Returns the instance after replacing values recursively.
     */
    final public function replace_values_recursive(array ...$replacements): static
    {
        $this->value = array_replace_recursive($this->value, ...$replacements);
        return $this;
    }

    /**
     * Replaces values in an array recursively with values from other arrays.
     *
     * Static alias of `array_replace_recursive`.
     *
     * @param array $array1 The array to be replaced.
     * @param array ...$replacements Arrays containing replacements.
     * @return static Returns a new instance after replacing values recursively.
     */
    final static public function replace_recursive(array $array1, array ...$replacements): static
    {
        return new self(array_replace_recursive($array1, ...$replacements));
    }

    /**
     * Reverses the order of the elements in this array.
     *
     * Alias of `array_reverse`.
     *
     * @param bool $preserve_keys Whether to preserve keys. Defaults to `false`.
     * @return static Returns the instance after reversing order.
     */
    final public function reverse(bool $preserve_keys = false): static
    {
        $this->value = array_reverse($this->value, $preserve_keys);
        return $this;
    }

    /**
     * Reverses the order of the elements in an array.
     *
     * Static alias of `array_reverse`.
     *
     * @param array $array The input array.
     * @param bool $preserve_keys Whether to preserve keys. Defaults to `false`.
     * @return static Returns a new instance after reversing order.
     */
    final static public function reverse_array(array $array, bool $preserve_keys = false): static
    {
        return new self(array_reverse($array, $preserve_keys));
    }

    /**
     * Removes duplicate values from this array.
     *
     * Alias of `array_unique`.
     *
     * @param int $flags Optional flags to use. Defaults to `SORT_STRING`.
     * @return static Returns the instance after removing duplicates.
     */
    final public function remove_duplicates(int $flags = SORT_STRING): static
    {
        $this->value = array_unique($this->value, $flags);
        return $this;
    }

    /**
     * Removes duplicate values from an array.
     *
     * Static alias of `array_unique`.
     *
     * @param array $array The input array.
     * @param int $flags Optional flags to use. Defaults to `SORT_STRING`.
     * @return static Returns a new instance after removing duplicates.
     */
    final static public function unique(array $array, int $flags = SORT_STRING): static
    {
        return new self(array_unique($array, $flags));
    }

    /**
     * Returns all the keys of the array.
     *
     * Alias of `array_keys`.
     *
     * @param mixed $filter_value Optional value to filter keys.
     * @param bool $strict Optional flag for strict comparison. Defaults to `false`.
     * @return array Returns an array of keys.
     */
    final public function get_keys(mixed $filter_value = null, bool $strict = false): array
    {
        return array_keys($this->value, $filter_value, $strict);
    }

    /**
     * Returns all the keys of the specified array.
     *
     * Static alias of `array_keys`.
     *
     * @param array $array The input array.
     * @param mixed $filter_value Optional value to filter keys.
     * @param bool $strict Optional flag for strict comparison. Defaults to `false`.
     * @return array Returns an array of keys.
     */
    final static public function keys(array $array, mixed $filter_value = null, bool $strict = false): array
    {
        return array_keys($array, $filter_value, $strict);
    }

    /**
     * Checks if a key exists in this array.
     *
     * Alias of `array_key_exists`.
     *
     * @param int|string $key The key to check.
     * @return bool Returns `true` if the key exists, `false` otherwise.
     */
    final public function has_key(int|string $key): bool
    {
        return array_key_exists($key, $this->value);
    }

    /**
     * Checks if a key exists in the specified array.
     *
     * Static alias of `array_key_exists`.
     *
     * @param array $array The input array.
     * @param int|string $key The key to check.
     * @return bool Returns `true` if the key exists, `false` otherwise.
     */
    final static public function key_exists(array $array, int|string $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * Searches for a value in this array and returns its key.
     *
     * Alias of `array_search`.
     *
     * @param mixed $value The value to search for.
     * @param bool $strict Optional flag for strict comparison. Defaults to `false`.
     * @return int|string|false Returns the key if found, `false` otherwise.
     */
    final public function get_key(mixed $value, bool $strict = false): int|string|false
    {
        return array_search($value, $this->value, $strict);
    }

    /**
     * Searches for a value in an array and returns its key.
     *
     * Static alias of `array_search`.
     *
     * @param array $array The input array.
     * @param mixed $value The value to search for.
     * @param bool $strict Optional flag for strict comparison. Defaults to `false`.
     * @return int|string|false Returns the key if found, `false` otherwise.
     */
    final static public function search(array $array, mixed $value, bool $strict = false): int|string|false
    {
        return array_search($value, $array, $strict);
    }

    /**
     * Applies a callback function to every element of this array.
     *
     * Alias of `array_walk`.
     *
     * @param callable $function The callback function.
     * @param mixed $args Optional additional arguments to pass to the callback.
     * @return static Returns the instance after walking the array.
     */
    final public function walk(callable $function, mixed $args = null): static
    {
        array_walk($this->value, $function, $args);
        return $this;
    }

    /**
     * Applies a callback function to every element of an array.
     *
     * Static alias of `array_walk`.
     *
     * @param array &$array The input array.
     * @param callable $function The callback function.
     * @param mixed $args Optional additional arguments to pass to the callback.
     * @return static Returns a new instance after walking the array.
     */
    final static public function walk_array(array &$array, callable $function, mixed $args = null): static
    {
        array_walk($array, $function, $args);
        return new self($array);
    }

    /**
     * Applies a callback function recursively to every element of this array.
     *
     * Alias of `array_walk_recursive`.
     *
     * @param callable $function The callback function.
     * @param mixed $args Optional additional arguments to pass to the callback.
     * @return static Returns the instance after walking the array recursively.
     */
    final public function walk_recursive(callable $function, mixed $args = null): static
    {
        array_walk_recursive($this->value, $function, $args);
        return $this;
    }

    /**
     * Applies a callback function recursively to every element of an array.
     *
     * Static alias of `array_walk_recursive`.
     *
     * @param array &$array The input array.
     * @param callable $function The callback function.
     * @param mixed $args Optional additional arguments to pass to the callback.
     * @return static Returns a new instance after walking the array recursively.
     */
    final static public function walk_recursive_array(array &$array, callable $function, mixed $args = null): static
    {
        array_walk_recursive($array, $function, $args);
        return new self($array);
    }

    /**
     * Sorts this array in ascending order.
     *
     * Alias of `sort` and `asort`.
     *
     * @param bool $maintain_keys Whether to maintain keys. Defaults to `true`.
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns the instance after sorting in ascending order.
     */
    final public function sort_ascending(bool $maintain_keys = true, int $flags = SORT_REGULAR): static
    {
        if ($maintain_keys) {
            asort($this->value, $flags);
        } else {
            sort($this->value, $flags);
        }
        return $this;
    }

    final public function custom_value_sort(callable $compare_function, bool $maintain_keys = false): static
    {
        if ($maintain_keys) {
            $this->value = usort($this->array, $compare_function);
        } else {
            $this->value = uasort($this->array, $compare_function);
        }
        return $this;
    }

    final public static function usort(array &$array, callable $callback): static
    {
        return new self(usort($array, $callback));
    }

    final public function custom_key_sort(callable $compare_function): static
    {
        $this->value = usort($this->array, $compare_function);
        return $this;
    }

    /**
     * Sorts this array in descending order.
     *
     * Alias of `rsort` and `arsort`.
     *
     * @param bool $maintain_keys Whether to maintain keys. Defaults to `true`.
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns the instance after sorting in descending order.
     */
    final public function sort_descending(bool $maintain_keys = true, int $flags = SORT_REGULAR): static
    {
        if ($maintain_keys) {
            arsort($this->value, $flags);
        } else {
            rsort($this->value, $flags);
        }
        return $this;
    }

    /**
     * Sorts the specified array in ascending order.
     *
     * Static alias of `sort` and `asort`.
     *
     * @param array $array The input array.
     * @param bool $maintain_keys Whether to maintain keys. Defaults to `true`.
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns a new instance after sorting in ascending order.
     */
    final static public function sort(array $array, bool $maintain_keys = true, int $flags = SORT_REGULAR): static
    {
        if ($maintain_keys) {
            asort($array, $flags);
        } else {
            sort($array, $flags);
        }
        return new self($array);
    }

    /**
     * Sorts the specified array in descending order.
     *
     * Static alias of `rsort` and `arsort`.
     *
     * @param array $array The input array.
     * @param bool $maintain_keys Whether to maintain keys. Defaults to `true`.
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns a new instance after sorting in descending order.
     */
    final static public function rsort(array $array, bool $maintain_keys = true, int $flags = SORT_REGULAR): static
    {
        if ($maintain_keys) {
            arsort($array, $flags);
        } else {
            rsort($array, $flags);
        }
        return new self($array);
    }

    /**
     * Sorts this array by key in ascending order.
     *
     * Alias of `ksort`.
     *
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns the instance after sorting by key in ascending order.
     */
    final public function sort_key_ascending(int $flags = SORT_REGULAR): static
    {
        ksort($this->value, $flags);
        return $this;
    }

    /**
     * Sorts the specified array by key in ascending order.
     *
     * Static alias of `ksort`.
     *
     * @param array $array The input array.
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns a new instance after sorting by key in ascending order.
     */
    final static public function ksort(array $array, int $flags = SORT_REGULAR): static
    {
        ksort($array, $flags);
        return new self($array);
    }

    /**
     * Sorts this array by key in descending order.
     *
     * Alias of `krsort`.
     *
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns the instance after sorting by key in descending order.
     */
    final public function sort_key_descending(int $flags = SORT_REGULAR): static
    {
        krsort($this->value, $flags);
        return $this;
    }

    /**
     * Sorts the specified array by key in descending order.
     *
     * Static alias of `krsort`.
     *
     * @param array $array The input array.
     * @param int $flags Optional flags for sorting. Defaults to `SORT_REGULAR`.
     * @return static Returns a new instance after sorting by key in descending order.
     */
    final static public function krsort(array $array, int $flags = SORT_REGULAR): static
    {
        krsort($array, $flags);
        return new self($array);
    }

    /**
     * Sorts this array in natural order.
     *
     * Alias of `natsort`.
     *
     * @param bool $case_sensitive Whether to perform case-sensitive sorting. Defaults to `false`.
     * @return static Returns the instance after sorting in natural order.
     */
    final public function sort_natural(bool $case_sensitive = false): static
    {
        if ($case_sensitive) {
            natsort($this->value);
        } else {
            natcasesort($this->value);
        }
        return $this;
    }


    /**
     * Sorts the specified array in natural order.
     *
     * Static alias of `natsort`.
     *
     * @param array $array The input array.
     * @param bool $case_sensitive Whether to perform case-sensitive sorting. Defaults to `false`.
     * @return static Returns a new instance after sorting in natural order.
     */
    final static public function natsort(array $array, bool $case_sensitive = false): static
    {
        if ($case_sensitive) {
            natsort($array);
        } else {
            natcasesort($array);
        }
        return new self($array);
    }

    /**
     * Creates an array containing variables and their values.
     *
     * Static alias of `compact`.
     *
     * @param array|string $var_name The variable name(s) to include in the array.
     * @param array|string ...$var_names Additional variable names to include.
     * @return static Returns a new instance with the created array.
     */
    final public static function compact(array|string $var_name, array|string ...$var_names): static
    {
        return new self(compact($var_name, ...$var_names));
    }

    /**
     * Randomly shuffles the elements of this array.
     *
     * Alias of `shuffle`.
     *
     * @return static Returns the instance after shuffling the array.
     */
    final public function shuffle(): static
    {
        shuffle($this->value);
        return $this;
    }

    /**
     * Randomly shuffles the elements of an array.
     *
     * Static alias of `shuffle`.
     *
     * @param array $array The input array.
     * @return static Returns a new instance after shuffling the array.
     */
    final static public function shuffle_array(array $array): static
    {
        shuffle($array);
        return new self($array);
    }

    final public static function explode(string $string, string $separator, int $limit = PHP_INT_MAX): static
    {
        return new self(explode($separator, $string, $limit));
    }

    final public function implode(string $separator = ""): string
    {
        return implode($separator, $this->value);
    }

    public function get_first_key(): int|string|null
    {
        return array_key_first($this->value);
    }
    public function get_first_element(): mixed
    {
        if ($key = $this->get_first_key()) {
            return $this->value[$key];
        }
        throw new \ValueError('empty array.');
    }
    public function get_last_key(): int|string|null
    {
        return array_key_last($this->value);
    }
    public function get_last_element(): mixed
    {
        if ($key = $this->get_last_key()) {
            return $this->value[$key];
        }
        throw new \ValueError('empty array.');
    }
}
