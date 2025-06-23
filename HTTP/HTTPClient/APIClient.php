<?php

namespace PHP_Library\HTTP\HTTPClient;

use PHP_Library\Error\Error;
use PHP_Library\Error\Notice;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader;
use PHP_Library\HTTP\HTTPClient\APIClient\AbstractPagination;
use SimpleXMLElement;

/**
 * A RESTful API client with pluggable pagination strategies and response consolidation.
 */
class APIClient extends HTTP1Client
{
    /** @var array Consolidated API response data. */
    public array $data = [];

    /** @var AbstractPagination|null Pagination strategy instance. */
    protected ?AbstractPagination $pagination;

    /** @var string|null Key pointing to primary list of items in each response. */
    protected ?string $primary_list_key = null;

    /** @var array Common field names for collections in paginated responses. */
    protected static array $primary_list_key_field_names = [
        'items',
        'collection',
        'results',
        'records',
        'entries',
        'elements',
        'rows',
    ];

    /**
     * Constructor.
     */
    public function __construct(
        string $url,
        string $default_method = 'GET',
        string $data = '',
        ?HTTPHeader $header = null,
        ?AbstractPagination $pagination = null
    ) {
        if ($header === null) {
            $header = new HTTPHeader();
        }

        $header->accept = 'application/json';

        $this->pagination = $pagination;
        parent::__construct($url, $default_method, $data, $header);
    }

    /**
     * Send the request and handle all paginated responses.
     */
    public function send(?string $method = null): static
    {
        do {
            $this->add_to_query($this->pagination->get_current_page_query());
            Notice::trigger($this->pagination->get_status_report());
            parent::send($method);

            if ($this->response->status_code !== 200) {
                throw new Error($this->host.": ".$this->response->reason_phrase ."\n".$this->response->raw_body, $this->response->status_code);
            }

            $this->update_data_from_raw_body();

            $this->pagination->prepare_next_page_query($this->data);
        } while ($this->pagination->has_next());

        return $this;
    }

    /**
     * Debug info representation.
     */
    public function __debugInfo(): array
    {
        $info = array_merge(
            parent::__debugInfo(),
            [
                'data' => $this->data,
                'response' => $this->response->start_line,
            ]
        );

        unset($info['raw_body']);
        return $info;
    }

    /**
     * Decode the raw response body and consolidate it into `$this->data`.
     */
    protected function update_data_from_raw_body(): static
    {
        $result = [];

        switch ($this->determine_content_type()) {
            case 'application/x-www-form-urlencoded':
                parse_str($this->response->raw_body, $result);
                break;

            case 'application/xml':
                $result = static::xml_parser($this->response->raw_body);
                break;

            case 'application/ld+json':
            case 'application/json':
                $result = json_decode($this->response->raw_body, true);
                break;

            default:
                break;
        }

        return $this->consolidate_data($result);
    }

    /**
     * Determine the content type of the current response.
     */
    protected function determine_content_type(): string
    {
        if (
            isset($this->response->header->content_type)
            && is_array($this->response->header->content_type)
        ) {
            return array_key_first($this->response->header->content_type);
        }

        return (string) ($this->response->header->content_type ?? '');
    }

    /**
     * Merge a newly parsed response into the global `$data` store.
     */
    protected function consolidate_data(array $new_data): static
    {
        if ($this->primary_list_key === null) {
            $this->primary_list_key = $this->detect_primary_key_name($new_data);
        }

        foreach ($new_data as $key => $value) {
            if (!isset($this->data[$key])) {
                $this->data[$key] = $value;
                continue;
            }

            if ($key === $this->primary_list_key && static::is_list_of_items($value)) {
                $this->data[$key] = array_merge($this->data[$key], $value);
            } else {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Detect the most likely field name representing the main item collection.
     */
    protected function detect_primary_key_name(array $data): string
    {
        foreach (array_keys($data) as $key) {
            if (
                in_array($key, static::$primary_list_key_field_names, true)
                && static::is_list_of_items($data[$key])
            ) {
                return $key;
            }
        }

        return '';
    }

    /**
     * Determine if a value is a list (numerically indexed array).
     */
    protected static function is_list_of_items(mixed $val): bool
    {
        return is_array($val) && array_keys($val) === range(0, count($val) - 1);
    }

    /**
     * Convert an XML string into an associative array.
     */
    protected static function xml_parser(SimpleXMLElement|string $xml_data): array
    {
        $xml = (array) simplexml_load_string($xml_data);

        if (empty($xml)) {
            return [];
        }

        foreach ($xml as $key => $value) {
            if (is_object($value) && strpos(get_class($value), 'SimpleXML') !== false) {
                $xml[$key] = static::xml_parser($value);
            }
        }

        return $xml;
    }
}
