<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload;

use PHP_Library\HTTP\HTTPClient\APIClient\APIClient;
use PHP_Library\HTTP\HTTPClient\APIClient\Error\APIClientError;

#[\AllowDynamicProperties]
/**
 * Class Item
 *
 * Represents a dynamic data payload used by the API client. 
 * This class allows dynamic property assignment at runtime 
 * and supports merging and selective copying of its properties.
 */
class Item extends PayloadContent 
{
    /**
     * Constructs an Item instance by dynamically assigning properties from the given array.
     *
     * @param array $item An associative array where each key-value pair becomes a dynamic property of this object.
     */
    public function __construct(array $item)
    {
        foreach ($item as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Merges a given array of key-value pairs into this Item instance.
     *
     * @param Item $item An associative array of properties to merge.
     * @param bool $override If false, existing properties will not be overwritten. Defaults to true.
     * @return static Returns the current Item instance with merged properties.
     */
    
    public function merge(PayloadContent $new_collection, bool $override = true): static
    {
        if(! $new_collection instanceof Item ){
            throw new APIClientError("Can not merge new Content into Item.");
        }
        foreach (get_object_vars($new_collection) as $property => $value) {
            if (!$override && isset($this->$property)) {
                continue;
            }
            $this->$property = $value;
        }
        return $this;
    }

    /**
     * Returns a copy of the current Item.
     *
     * - If $properties is null, returns a full clone of the object.
     * - Otherwise, creates a new Item with only the specified properties.
     *   Missing properties throw an exception unless $throw_error is false.
     *
     * @param string[]|null $properties  Properties to copy, or null to clone everything.
     * @param bool $throw_error          Whether to throw on missing properties (default: true).
     *
     * @return static
     * @throws APIClientError If a property is missing and $throw_error is true.
     */

    public function copy(?array $properties = null, bool $throw_error = true): static
    {
        if (!$properties) {
            return clone $this;
        }
        $copy = [];
        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                if ($throw_error) {
                    throw new APIClientError("'$property' is not a property of this item.");
                }
                $copy[$property] = null;
            } else {
                $copy[$property] = $this->$property;
            }
        }
        return new Item($copy);
    }

    public function __get(string $property_name): mixed {
        if(!property_exists($this, $property_name)) {
            $property_similarities = [];
            foreach(get_object_vars($this) as $object_property_name => $value) {
                $property_similarities[$object_property_name] = similar_text($object_property_name, $property_name, $percent);
            }
            asort($property_similarities);
            $best_match = array_key_first($property_similarities);
            throw new APIClientError("'$property_name' is not part of this item. did you mean '$best_match'");
        }
        return $this->$property_name;
    }
}
