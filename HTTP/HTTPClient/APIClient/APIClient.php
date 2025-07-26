<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient;

use PHP_Library\Error\Notice;
use PHP_Library\HTTP\HTTPClient\Auth\AbstractAuth;
use PHP_Library\HTTP\HTTPClient\APIClient\Error\APIClientError;
use PHP_Library\HTTP\HTTPClient\APIClient\Pagination\AbstractPagination;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Item;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Payload;
use PHP_Library\HTTP\HTTPClient\HTTP1Client;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader\HTTPRequestHeader;
use SimpleXMLElement;

/**
 * A RESTful HTTP/1.1 API client with pluggable pagination and payload consolidation.
 */
class APIClient extends HTTP1Client
{
    /** @var Payload|null Consolidated payload from all HTTP responses. */
    protected ?Payload $payload = null;

    /** @var AbstractPagination|null Pagination strategy handler. */
    protected ?AbstractPagination $pagination;

    /**
     * APIClient constructor.
     *
     * @param string $url Endpoint URL.
     * @param string $default_method Default HTTP method.
     * @param string|array $data Request body.
     * @param HTTPRequestHeader|null $header Optional headers.
     * @param AbstractPagination|null $pagination Optional pagination handler.
     * @param AbstractAuth|null $auth Optional authentication handler.
     */
    public function __construct(string $url, string $default_method = 'GET', string|array $data = '', ?HTTPRequestHeader $header = null, ?AbstractPagination $pagination = null, ?AbstractAuth $auth = null)
    {
        if (is_null($header)) {
            $header = new HTTPRequestHeader();
        }

        $header->accept = 'application/json';
        $this->pagination = $pagination;

        parent::__construct($url, $default_method, $data, $header, auth: $auth);
    }

    /**
     * Send a paginated request series and collect the payload.
     *
     * @param string|null $method HTTP method override.
     * @param int|null $max_requests Max number of pages to request.
     * @param int|null $max_elements Max total elements to collect.
     * @param int|null $page_size Number of elements per page.
     * @param float|null $request_delay Delay between requests (seconds).
     * @return static
     */
    public function send(?string $method = null, ?int $max_requests = null, ?int $max_elements = null, ?int $page_size = null, ?float $request_delay = null): static
    {
        if (!is_null($this->pagination)) {
            $this->pagination->set_limits($max_requests, $max_elements, $page_size, $request_delay);
        }

        do {
            if ($this->pagination) {
                $this->add_to_query($this->pagination->get_current_page_query());
            }

            parent::send($method);
            $this->update_payload_from_raw_body();

            if (!isset($this->pagination)) {
                $auto_pagination = AbstractPagination::create_from_first_responses_meta($this->payload->get_meta());
                $this->pagination = $auto_pagination ?: null;

                if (!is_null($this->pagination)) {
                    $this->pagination->set_limits($max_requests, $max_elements, $page_size, $request_delay);
                }
            }

            if (!is_null($this->pagination)) {
                $this->pagination->prepare_next_page_query($this->payload);
                Notice::trigger($this->payload->count() . " Elements from " . $this->pagination->count_requests() . " requests.");
            }
        } while (!is_null($this->pagination) && $this->pagination->has_next($this->payload->count()));

        return $this;
    }

    /**
     * Select specific fields from each payload item.
     *
     * @param string ...$keys Fields to extract.
     * @return array|false Selected fields or false on invalid structure.
     * @throws APIClientError
     */
    public function select_payload(string ...$keys): false|array
    {
        if ($this->payload->is_single_item()) {
            return [$this->payload->get_item(...$keys)];
        }

        if ($this->payload->is_collection()) {
            return $this->payload->collection_select(...$keys);
        }

        return false;
    }

    /**
     * Retrieve all payload content as items or collection.
     *
     * @return array Payload content.
     */
    public function get_payload(): array
    {
        return $this->select_payload();
    }

        /**
     * Retrieve singular item or first item of collection.
     *
     * @return Item Payload item.
     */
    public function get_item(): Item
    {
        return $this->select_payload()[0];
    }

    /**
     * Retrieve metadata from the payload.
     *
     * @param string|null $key Optional key to extract a specific value.
     * @return array Metadata or specific metadata value.
     */
    public function get_payload_meta(?string $key = null): array
    {
        return $this->payload->get_meta($key);
    }

    /**
     * Retrieve error information from the payload.
     *
     * @param string|null $key Optional error key.
     * @return array Error list or specific error value.
     */
    public function get_payload_error(?string $key = null): array
    {
        return $this->payload->get_error($key);
    }

    /**
     * Count number of items in the payload.
     *
     * @return int Payload item count.
     */
    public function count_payload_items(): int
    {
        return $this->payload->count();
    }

    /**
     * Return internal debug information for inspection.
     *
     * @return array<string,mixed> Structured debug data.
     */
    public function __debug_info(): array
    {
        $debug_info = [];

        if (isset($this->payload)) {
            $debug_info['payload'] = $this->payload->is_single_item()
                ? $this->payload->get_item()
                : $this->payload->get_collection();
        }

        if (isset($this->response)) {
            $debug_info['response'] = $this->response->start_line;
        }

        return array_merge(parent::__debugInfo(), $debug_info);
    }

    /**
     * Decode the HTTP response body and store it as a Payload.
     *
     * @return static
     * @throws APIClientError On invalid or malformed body content.
     */
    protected function update_payload_from_raw_body(): static
    {
        $result = [];

        switch ($this->determine_content_type()) {
            case 'application/x-www-form-urlencoded':
                parse_str($this->response->raw_body, $result);
                break;

            case 'application/xml':
                $result = static::xml_parser($this->response->raw_body);
                break;

            case 'application/json':
            case 'application/ld+json':
                $result = json_decode($this->response->raw_body, true);
                if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new APIClientError("Invalid JSON in response body");
                }
                break;

            default:
                // Unsupported content type
                break;
        }

        if ($this->payload) {
            $this->payload->consolidate_payload(new Payload($result));
        } else {
            $this->payload = new Payload($result);
        }

        return $this;
    }

    /**
     * Determine the Content-Type of the response body.
     *
     * @return string Parsed Content-Type value.
     */
    protected function determine_content_type(): string
    {
        $content_type = $this->response->get_header_field('content_type') ?? '';
        $content_type = explode(';', $content_type, 2)[0];

        return is_array($content_type) ? array_key_first($content_type) : (string) $content_type;
    }

    /**
     * Recursively parse XML content into a PHP array.
     *
     * @param SimpleXMLElement|string $xml_data Raw XML string or parsed node.
     * @return array<string,mixed> Converted XML structure.
     */
    protected static function xml_parser(SimpleXMLElement|string $xml_data): array
    {
        $xml = (array) simplexml_load_string($xml_data);

        foreach ($xml as $key => $value) {
            if (is_object($value) && str_starts_with(get_class($value), 'SimpleXML')) {
                $xml[$key] = static::xml_parser($value);
            }
        }

        return $xml;
    }
}
