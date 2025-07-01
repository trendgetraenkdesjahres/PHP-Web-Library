<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload;

use ArrayAccess;
use ArrayIterator;
use PHP_Library\HTTP\HTTPClient\APIClient\Error\APIClientError;

/**
 * Payload wrapper for a decoded API response.
 *
 * Contains either a single resource (`$item`) or a collection (`$collection`),
 * with optional metadata and errors. Implements iterable, array access, and countable.
 */
class Payload implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var string[] Common keys expected to contain the primary list of resources. */
    protected static array $primary_list_key = [
        'items',
        'collection',
        'results',
        'records',
        'entries',
        'elements',
        'rows',
    ];

    /** @var string[] Keys typically used to indicate error blocks. */
    protected static array $error_key = [
        'errors',
        'error'
    ];

    /** @var string[] Keys commonly used for metadata. */
    protected static array $meta_key = [
        'meta',
        'pagination',
        'paging'
    ];

    /** @var Item[]|null List of resource items if plural. */
    protected ?array $collection = null;

    /** @var Item|null A single resource item. */
    protected ?Item $item = null;

    /** @var array<string,mixed> Metadata (pagination, counts, etc.). */
    protected array $meta = [];

    /** @var array<string,mixed> Errors returned in the response. */
    protected array $errors = [];

    /** @var array<int|string,mixed> The raw, untouched response data. */
    protected readonly array $raw;

    /**
     * Constructor.
     *
     * Parses raw API response, detects standard keys, and builds internal structure.
     *
     * @param array $data Raw decoded API response.
     * @param string|null $collection_key Override for item list key.
     * @param string|null $meta_key Override for metadata key.
     * @param string|null $error_key Override for error key.
     */
    public function __construct(array $data, ?string $collection_key = null, ?string $meta_key = null, ?string $error_key = null)
    {
        $this->raw = $data;

        $collection_key ??= $this->detect_primary_key($data);
        $meta_key ??= $this->detect_meta_key($data);
        $error_key ??= $this->detect_error_key($data);

        foreach ($data as $key => $value) {
            switch (true) {
                case $key === $collection_key && static::is_list_of_items($value) && is_null($this->item):
                    $this->collection = array_map(static fn(array $item) => new Item($item), $value);
                    break;

                case $key === $collection_key && is_null($this->collection):
                    $this->item = new Item($value);
                    break;

                case $key === $meta_key:
                    $this->meta = $value;
                    break;

                case $key === $error_key:
                    $this->errors = $value;
                    break;

                default:
                    if (is_null($this->collection)) {
                        if ($this->item === null) {
                            $this->item = new Item([$key => $value]);
                        } else {
                            $this->item->merge([$key => $value]);
                        }
                    } else {
                        // fallback to meta if item is already filled
                        $this->meta[$key] = $value;
                    }
            }
        }

        if ($this->collection === null && $this->item === null) {
            $this->item = new Item($data);
        }
    }

    /**
     * Merge another payload into this one.
     *
     * @param self $new_payload The incoming payload to merge.
     * @return static
     */
    public function consolidate_payload(self $new_payload): static
    {
        if ($this->is_single_item()) {
            $this->collection = [$this->item];
            $this->item = null;

            $incoming = $new_payload->is_single_item()
                ? [$new_payload->item]
                : $new_payload->collection;

            $this->collection = array_merge($this->collection, $incoming);
        } elseif ($this->is_collection()) {
            $incoming = $new_payload->is_single_item()
                ? [$new_payload->item]
                : $new_payload->collection;

            $this->collection = array_merge($this->collection, $incoming);
        }

        $this->meta = array_merge($this->meta, $new_payload->meta);
        $this->errors = array_merge($this->errors, $new_payload->errors);

        return $this;
    }

    /**
     * Return raw decoded response.
     */
    public function to_array(): array
    {
        return $this->raw;
    }

    /**
     * Check if payload holds a collection of items.
     */
    public function is_collection(): bool
    {
        return $this->collection !== null;
    }

    /**
     * Check if payload holds a single item.
     */
    public function is_single_item(): bool
    {
        return $this->item !== null;
    }

    /**
     * Return a subset of fields for each item in the collection.
     *
     * @param string ...$key Field names to extract.
     * @return array<Item>
     * @throws APIClientError
     */
    public function collection_select(string ...$key): array
    {
        if (! $this->is_collection()) {
            throw new APIClientError("No collection.");
        }
        $result = [];
        foreach ($this->collection as $item) {
            $result[] = $item->copy($key);
        }
        return $result;
    }

    /**
     * Get the collection or a list of selected fields from each item.
     *
     * @param string|null $key First field.
     * @param string ...$keys Additional fields.
     * @return array<Item>|false
     */
    public function get_collection(?string $key = null, string ...$keys): false|array
    {
        if (! $this->is_collection()) {
            return false;
        }

        if (is_null($key)) {
            return $this->collection;
        }

        $result = [];
        foreach ($this->collection as $item) {
            $result[] = $item->copy([$key, ...$keys], false);
        }

        return $result;
    }

    /**
     * Get a metadata value or all metadata.
     *
     * @param string|null $key
     * @return mixed
     * @throws APIClientError
     */
    public function get_meta(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->meta;
        }

        if (!isset($this->meta[$key])) {
            throw new APIClientError("meta key '$key' is not set.");
        }

        return $this->meta[$key];
    }

    /**
     * List of available metadata keys.
     *
     * @return string[]
     */
    public function get_meta_keys(): array
    {
        return array_keys($this->meta);
    }

    /**
     * Get an error value or all errors.
     *
     * @param string|null $key
     * @return mixed
     * @throws APIClientError
     */
    public function get_error(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->errors;
        }

        if (!isset($this->errors[$key])) {
            throw new APIClientError("error key '$key' is not set.");
        }

        return $this->errors[$key];
    }

    /**
     * Iterator for contained items.
     *
     * @return ArrayIterator<int, Item>
     */
    public function getIterator(): ArrayIterator
    {
        if ($this->collection !== null) {
            return new ArrayIterator($this->collection);
        }

        return new ArrayIterator($this->item ? [$this->item] : []);
    }

    public function count(): int
    {
        return $this->is_collection()
            ? count($this->collection)
            : ($this->item ? 1 : 0);
    }

    public function offsetExists($offset): bool
    {
        return $offset === 0 && $this->is_single_item()
            ? true
            : isset($this->collection[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        if ($offset === 0 && $this->is_single_item()) {
            return $this->item;
        }

        return $this->collection[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        if ($offset === 0 && $this->is_single_item()) {
            $this->item = null;
            return;
        }

        unset($this->collection[$offset]);
    }

    /**
     * Detect which key holds the primary list of resources.
     */
    private function detect_primary_key(array $data): string
    {
        foreach (array_keys($data) as $key) {
            if (
                in_array($key, static::$primary_list_key, true) &&
                static::is_list_of_items($data[$key])
            ) {
                return $key;
            }
        }
        return '';
    }

    /**
     * Detect the key holding metadata.
     */
    private function detect_meta_key(array $data): string
    {
        foreach (array_keys($data) as $key) {
            if (in_array($key, static::$meta_key, true)) {
                return $key;
            }
        }
        return '';
    }

    /**
     * Detect the key holding error data.
     */
    private function detect_error_key(array $data): string
    {
        foreach (array_keys($data) as $key) {
            if (in_array($key, static::$error_key, true)) {
                return $key;
            }
        }
        return '';
    }

    /**
     * Check if a value is a numerically indexed array (0-based).
     */
    protected static function is_list_of_items(mixed $val): bool
    {
        return is_array($val) && array_keys($val) === range(0, count($val) - 1);
    }
}
