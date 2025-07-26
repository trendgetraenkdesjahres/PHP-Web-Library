<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload;

use PHP_Library\HTTP\HTTPClient\APIClient\Error\APIClientError;

class Collection extends PayloadContent implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /** @var string[] Common keys expected to contain the primary list of resources. */
    protected static array $collection_list_key = [
        'items',
        'collection',
        'results',
        'result',
        'records',
        'entries',
        'elements',
        'rows',
    ];

    /** @var Item[] */
    protected array $items;

    public function __construct(array $data)
    {
        $this->items = array_map(fn(array $item) => new Item($item), $data);
    }

    public function select(string ...$fields): array
    {
        return array_map(fn(Item $item) => $item->copy($fields, false), $this->items);
    }

    public function merge(PayloadContent $new_collection, bool $override = true): static
    {
        if (! $new_collection instanceof Collection) {
            throw new APIClientError("Can not merge new Content into Collection.");
        }
        $this->items = [...$this->items, ...$new_collection->items];
        return $this;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): ?Item
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Item) {
            throw new \InvalidArgumentException("Only Item instances can be added.");
        }
        $offset === null ? $this->items[] = $value : $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function first(): ?Item
    {
        return $this->items[0] ?? null;
    }

    public function to_array(): array
    {
        return $this->items;
    }

    /**
     * Check if a value is a numerically indexed array (0-based).
     */
    public static function is_list_of_items(mixed $val): bool
    {
        return is_array($val) && array_keys($val) === range(0, count($val) - 1);
    }

    /**
     * Detect which key holds the primary list of resources.
     */
    public static function find_collection_key(array $data, bool $check_if_list = true): string|false
    {
        foreach (array_keys($data) as $key) {
            if (
                in_array($key, static::$collection_list_key, true)
            ) {
                if ($check_if_list && ! static::is_list_of_items($data[$key], true)) {
                    return false;
                }
                return $key;
            }
        }
        return false;
    }
}
