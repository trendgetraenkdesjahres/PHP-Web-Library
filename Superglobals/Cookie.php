<?php

namespace PHP_Library\Superglobals;

use PHP_Library\Superglobals\Error\CookieError;

class Cookie
{
    protected static array $cookie_parameters = [];

    /**
     * returns false if cookie is not set.
     *
     * @param [type] $name
     * @return string|array|integer|float|false
     */
    public static function get($name): string|array|int|false
    {
        if (!isset($_COOKIE[$name])) {
            return false;
        }
        $value = $_COOKIE[$name];
        if (
            isset(static::$cookie_parameters[$name])
            && isset(static::$cookie_parameters[$name]['type'])
        ) {
            $type = static::$cookie_parameters[$name]['type'];
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
     * Send a cookie. Will not work if output is already send. Values will be avaible when working on the next request using `Cookie::get($name)`.
     * @link https://php.net/manual/en/function.setcookie.php
     * @param string $name The name of the cookie.
     * @param string|array|int $value The value of the cookie. It is stored on the client; do not store sensitive information.
     * @param int|null $expire_in_seconds The number of seconds before the cookie is expired. If set to 0, the cookie will expire at the end of the session (when the browser closes). If set to 'null' and the browser will delete it.
     * @param string $path The path on the server in which the cookie will be available on.
     * @param string $domain The domain that the cookie is available.
     * @param bool $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
     * @param bool $httponly When true the cookie will be made accessible only through the HTTP protocol.
     * @return bool Success.
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
