<?php

namespace PHP_Library\Superglobals\PHPTraits;

trait QueryTrait
{
    /**
     * Parse a query string into an array.
     *
     * @param string [optional] $query The query string to parse. If not given, it parses $_SERVER["QUERY_STRING"].
     *
     * @return array The parsed query as an array.
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
                    $return[$key_value[0]] = $key_value[1];
                } else {
                    $return[$value] = true;
                }
            }
            return $return;
        }
        return [];
    }
}
