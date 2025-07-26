<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload;

use PHP_Library\HTTP\HTTPClient\APIClient\Error\APIClientError;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier\KeyNameScorer;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier\PayloadClassifier;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier\RootContainerScorer;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier\ValueStructureScorer;

/**
 * Payload wrapper for a decoded API response.
 *
 * Represents either a single resource (`$content` as Item) or a resource collection (`$content` as Collection),
 * with optional metadata and error information. Implements Countable interface.
 */
class Payload implements \Countable
{
    /** @var Item|Collection Main response content */
    protected Item|Collection $content;

    /** @var array<string,mixed> Metadata related to the payload (e.g., pagination) */
    protected array $meta = [];

    /** @var array<string,mixed> Error information returned by the API */
    protected array $error = [];

    /** @var array<int|string,mixed> Unmodified decoded response body */
    protected readonly array $raw;

    /**
     * @param array $data Raw decoded API response
     */
    public function __construct(array $data)
    {
        $this->raw = $data;

        $classifier = new PayloadClassifier([
            KeyNameScorer::class,
            ValueStructureScorer::class,
            RootContainerScorer::class,
        ]);

        $classification = $classifier->classify($data);

        $this->meta = $classification->get_meta();
        $this->error = $classification->get_error();

        $content_data = $classification->get_content();

        if (empty($content_data)) {
            $this->content = new Item($data);
        } elseif (Collection::is_list_of_items($content_data)) {
            $this->content = new Collection($content_data);
        } else {
            $this->content = new Item($content_data);
        }
    }

    /**
     * Merge another payload into this one.
     *
     * @param self $new_payload Payload instance to merge
     * @return static
     */
    public function consolidate_payload(self $new_payload): static
    {
        $this->content->merge($new_payload->get_content());
        $this->meta = array_merge($this->meta, $new_payload->get_meta());
        $this->error = array_merge($this->error, $new_payload->get_error());
        return $this;
    }

    /**
     * Get the raw decoded response as an array.
     *
     * @return array
     */
    public function to_array(): array
    {
        return $this->raw;
    }

    /**
     * Check if the payload holds a collection.
     *
     * @return bool
     */
    public function is_collection(): bool
    {
        return $this->content instanceof Collection;
    }

    /**
     * Check if the payload holds a single item.
     *
     * @return bool
     */
    public function is_single_item(): bool
    {
        return $this->content instanceof Item;
    }

    /**
     * Return the internal content (cloned).
     *
     * @return Item|Collection
     */
    public function get_content(): Item|Collection
    {
        return clone $this->content;
    }

    /**
     * Return a specific item, optionally narrowed to selected fields.
     *
     * @param string ...$key Field names
     * @return Item|false
     */
    public function get_item(string ...$key): false|Item
    {
        if ($this->is_single_item()) {
            return $this->content->copy($key, false);
        }

        if ($this->is_collection()) {
            $array = $this->content->to_array();
            return $this->content[array_key_first($array)]->copy($key, false);
        }

        return false;
    }

    /**
     * Return a filtered list of items with selected fields.
     *
     * @param string ...$key Field names
     * @return array<Item>
     * @throws APIClientError
     */
    public function collection_select(string ...$key): array
    {
        if (! $this->is_collection()) {
            throw new APIClientError("No collection.");
        }

        $result = [];
        foreach ($this->content as $item) {
            $result[] = $item->copy($key);
        }

        return $result;
    }

    /**
     * Return the full collection or a subset of fields for each item.
     *
     * @param string|null $key Optional first field
     * @param string ...$keys Additional fields
     * @return array<Item>|false
     */
    public function get_collection(?string $key = null, string ...$keys): false|array
    {
        if (! $this->is_collection()) {
            return false;
        }

        if (is_null($key)) {
            return $this->content->to_array();
        }

        $result = [];
        foreach ($this->content as $item) {
            $result[] = $item->copy([$key, ...$keys], false);
        }

        return $result;
    }

    /**
     * Get a metadata value or all metadata.
     *
     * @param string|null $key Metadata key or null for all
     * @return mixed
     * @throws APIClientError
     */
    public function get_meta(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->meta;
        }

        if (! isset($this->meta[$key])) {
            throw new APIClientError("meta key '$key' is not set.");
        }

        return $this->meta[$key];
    }

    /**
     * List all metadata keys.
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
     * @param string|null $key Error key or null for all
     * @return mixed
     * @throws APIClientError
     */
    public function get_error(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->error;
        }

        if (! isset($this->error[$key])) {
            throw new APIClientError("error key '$key' is not set.");
        }

        return $this->error[$key];
    }

    /**
     * Return the number of items in the content.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->is_collection() ? count($this->content) : 1;
    }

    /**
     * Return object info for var_dump and debugging.
     *
     * Excludes raw response to avoid clutter.
     *
     * @return array<string,mixed>
     */
    public function __debugInfo(): array
    {
        $info = [];
        $property_reflections = (new \ReflectionClass(static::class))->getProperties();

        foreach ($property_reflections as $property_reflection) {
            $property_name = $property_reflection->getName();

            if ($property_name === 'raw') {
                continue;
            }

            if ($property_reflection->isStatic()) {
                $info["::" . $property_name] = static::$$property_name ?? null;
            } else {
                $info[$property_name] = $this->$property_name ?? null;
            }
        }

        return $info;
    }
}
