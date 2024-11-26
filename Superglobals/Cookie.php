<?php

namespace PHP_Library\Superglobals;

use PHP_Library\Error\Warning;

/**
 * Class Cookie
 *
 * Provides a utility class for handling HTTP cookies, including setting, retrieving, and unsetting cookies.
 * Includes support for typed cookie values and array-based cookies.
 */
class Cookie
{
    /**
     * @var array Stores parameters for cookies such as type, path, domain, secure, and httponly attributes.
     */
    protected static array $cookie_parameters = [];

    /**
     * Checks if a cookie with the specified key exists.
     *
     * @param string $key The name of the cookie to check.
     * @return bool True if the cookie exists, false otherwise.
     */
    public static function has_field(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Retrieves the value of a cookie by its key.
     * Returns the value in its appropriate type if specified or defaults to string.
     * Triggers a warning if the cookie is undefined.
     *
     * @param string $key The name of the cookie to retrieve.
     * @return mixed The value of the cookie, or null if it does not exist.
     */
    public static function get(string $key): mixed
    {
        if (!static::has_field($key)) {
            Warning::trigger("Undefined Cookie Field '{$key}'");
            return null;
        }
        $value = $_COOKIE[$key];
        if (
            isset(static::$cookie_parameters[$key])
            && isset(static::$cookie_parameters[$key]['type'])
        ) {
            $type = static::$cookie_parameters[$key]['type'];
            if ($type === 'array') {
                return static::decode_str_to_array($value);
            }
            return settype($value, $type);
        }
        if (ctype_digit($value)) {
            return (int) $value;
        }
        if ($array = static::decode_str_to_array($value)) {
            return $array;
        }
        return (string) $value;
    }

    /**
     * Sets a cookie with the specified attributes.
     * The cookie will be available on subsequent requests.
     *
     * @link https://php.net/manual/en/function.setcookie.php
     * @param string $name The name of the cookie.
     * @param string|array|int|float $value The value of the cookie. Sensitive information should not be stored.
     * @param int|null $expire_in_seconds The expiration time in seconds. Null means the cookie expires when the browser closes.
     * @param string $path The path where the cookie is accessible.
     * @param string $domain The domain where the cookie is accessible.
     * @param bool $secure Whether the cookie is transmitted over HTTPS only.
     * @param bool $httponly Whether the cookie is accessible only via HTTP protocol.
     * @return bool True if the cookie is successfully set, false otherwise.
     */
    public static function set(string $name, string|array|int|float $value, ?int $expire_in_seconds = 0, string $path = "", string $domain = "", bool $secure = false, bool $httponly = false): bool
    {
        if (is_null($expire_in_seconds)) {
            $expires_or_options = 1;
        } else if ($expire_in_seconds === 0) {
            $expires_or_options = $expire_in_seconds;
        } else {
            $expires_or_options = time() + $expire_in_seconds;
        }
        static::$cookie_parameters[$name] = [
            'type' => gettype($value),
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        ];
        if (is_array($value)) {
            $value = http_build_query($value, encoding_type: PHP_QUERY_RFC3986);
        }
        return setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
    }


    /**
     * Deletes one or more cookies by their names.
     * Removes them from both the client and the internal cookie parameters array.
     *
     * @param string ...$names The names of the cookies to unset.
     * @return bool True if all cookies are successfully unset, false otherwise.
     */
    public static function unset(string ...$names): bool
    {
        foreach ($names as $name) {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
            }
            if (isset(static::$cookie_parameters[$name])) {
                $path = static::$cookie_parameters[$name]['path'];
                $domain = static::$cookie_parameters[$name]['domain'];
                $secure = static::$cookie_parameters[$name]['secure'];
                $httponly = static::$cookie_parameters[$name]['httponly'];
                if (!static::set($name, '', null, $path, $domain, $secure, $httponly)) {
                    return false;
                }
            } else {
                if (!static::set($name, '', null)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Decodes a URL-encoded string into an associative array.
     * Used to handle array-based cookies.
     *
     * @param string $string The URL-encoded string to decode.
     * @return array|false The decoded array, or false if the string cannot be decoded.
     */
    protected static function decode_str_to_array(string $string): array|false
    {
        $values = [];
        if (!$key_value_strings = explode('&', $string)) {
            return false;
        }
        foreach (explode('&', $string) as $key_value_str) {
            $kv = explode('=', $key_value_str);
            if (count($key_value_strings) === 1 && count($kv) === 1) {
                return false;
            }
            $key = urldecode($kv[0]);
            $value = isset($kv[1]) ? urldecode($kv[1]) : true;
            $values[$key] = $value;
        }
        return $values;
    }
}
