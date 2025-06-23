<?php

namespace PHP_Library\HTTP\HTTPMessage;

#[\AllowDynamicProperties]
class HTTPHeader
{
    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    public null|string|array $cache_control;


    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection
     */
    public ?string $connection;
    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary
     */
    public ?string $vary;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept
     */
    public  null|string|array $accept;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     */
    public  null|string|array $date;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Pragma
     */
    public ?string $pragma;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade
     */
    public ?string $upgrade;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Transfer-Encoding
     */
    public ?string $transfer_encoding;

    /**
     * @var int|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length
     */
    public ?int $content_length;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
     */
    public ?string $content_encoding;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     */
    public null|string|array $content_type;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Location
     */
    public ?string $location;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Retry-After
     */
    public ?string $retry_after;

    /**
     * @var string|null
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
     */
    public ?string $x_forwarded_for;

    /**
     * 
     * @var string[]
     */
    private array $_raw_fields = [];

    public function __construct(array $header_data = [])
    {
        if (!$header_data) {
            return;
        }
        foreach ($header_data as $i => $header_row) {
            if (is_string($i)) {
                continue;
            }
            if (! ($key_value_pair = explode(":", $header_row))) {
                continue;
            }
            $header_data[$key_value_pair[0]] = trim($key_value_pair[1]);
            unset($header_data[$i]);
        }

        $this->_raw_fields = $header_data;
        foreach ($this->_raw_fields as $property => $value) {
            $php_property_name = strtolower(str_replace("-", "_", $property));
            $this->set_property($php_property_name, $value);
        }
    }

    public function __debugInfo()
    {
        $info = [];
        $reflection = new \ReflectionClass(static::class);
        foreach ($reflection->getProperties() as $property_reflection) {
            $property_name = $property_reflection->name;
            if (!isset($this->$property_name)) {
                continue;
            }
            if (str_starts_with($property_name, "_")) {
                continue;
            }
            $info[$property_name] = $this->$property_name;
        }
        return $info;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        if (isset($this->_raw_fields[$name])) {
            return $this->_raw_fields[$name];
        }
    }

    public function add_field(string $field, string $value): static
    {
        $this->_raw_fields[$field] = $value;
        return $this;
    }

    public function to_array(): array
    {
        return $this->__debugInfo();
    }

    public function __toString()
    {
        return implode(PHP_EOL, $this->to_array());
    }

    protected function set_property($property, $value): static
    {
        $parts = preg_split(
            '/(?:;|,)(?![^"\[\]]*[\]\"])|(?:;|,)(?![^\'\[\]]*[\]\'])/',
            $value,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        if (count($parts) === 1) {
            $this->$property = $parts[0];
            return $this;
        }
        foreach ($parts as $part) {
            $subparts = preg_split(
                '/=(?![^"\[\]]*[\]\"])|=(?![^\'\[\]]*[\]\'])/',
                $part,
                -1,
                PREG_SPLIT_NO_EMPTY
            );
            if (!isset($this->$property)) {
                $this->$property = [];
            }
            if (count($subparts) === 1) {
                $this->$property[trim($subparts[0])] = true;
                continue;
            }
            $this->$property[trim($subparts[0])] = trim($subparts[1]);
        }
        return $this;
    }
}
