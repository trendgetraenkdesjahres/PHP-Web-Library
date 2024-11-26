<?php

namespace PHP_Library\Superglobals\PHPTraits;

/**
 * Trait QueryTrait
 *
 * Provides functionality for parsing query strings into associative arrays.
 * This trait is commonly used for handling and parsing HTTP query strings.
 */
trait QueryTrait
{
    /**
     * Parse a query string into an associative array.
     *
     * If no query string is provided, it defaults to parsing the value of `$_SERVER["QUERY_STRING"]`.
     *
     * @param string|null $query Optional. The query string to parse. Defaults to `$_SERVER["QUERY_STRING"]` if not provided.
     *
     * @return array The parsed query string as an associative array. Keys without values are set to `true`.
     */
    protected static function parse_query(?string $query = null): array
    {
        if (!$query) {
            $query = $_SERVER["QUERY_STRING"];
        }
        if ($query) {
            $return = [];
            $array = explode('&', $query);
            foreach ($array as $value) {
                if (strpos($value, '=')) {
                    $key_value = explode('=', $value);
                    $return[$key_value[0]] = urldecode($key_value[1]);
                } else {
                    $return[$value] = true;
                }
            }
            return $return;
        }
        return [];
    }
}
