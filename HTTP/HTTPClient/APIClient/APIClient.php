<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient;

use PHP_Library\Error\Notice;
use PHP_Library\HTTP\HTTPClient\AbstractAuth;
use PHP_Library\HTTP\HTTPClient\APIClient\Error\APIClientError;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader;
use PHP_Library\HTTP\HTTPClient\APIClient\Pagination\AbstractPagination;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Payload;
use PHP_Library\HTTP\HTTPClient\Error\HTTPClientError;
use PHP_Library\HTTP\HTTPClient\HTTP1Client;
use SimpleXMLElement;

/**
 * A RESTful API client with pluggable pagination strategies and response consolidation.
 */
class APIClient extends HTTP1Client
{
    /** @var null|Payload Consolidated API response data. */
    public ?Payload $payload = null;
    /** @var AbstractPagination|null Pagination strategy instance. */
    protected ?AbstractPagination $pagination;

    /**
     * Constructor.
     *
     * @param string $url
     * @param string $default_method
     * @param string $data
     * @param HTTPHeader|null $header
     * @param AbstractPagination|null $pagination
     */
    public function __construct(
        string $url,
        string $default_method = 'GET',
        string $data = '',
        ?HTTPHeader $header = null,
        ?AbstractPagination $pagination = null,
        ?AbstractAuth $auth = null
    ) {
        if ($header === null) {
            $header = new HTTPHeader();
        }

        $this->auth = $auth;

        $header->accept = 'application/json';

        $this->pagination = $pagination;
        parent::__construct($url, $default_method, $data, $header);
    }

    /**
     * Send the request and handle all paginated responses.
     *
     * @param string|null $method
     * @return static
     * @throws HTTPClientError When response status code is not 200.
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
            if ($this->response->status_code !== 200) {
                throw new APIClientError("Unexpected status code {$this->response->status_code}");
            }
            $this->update_payload_from_raw_body();
            if (!isset($this->pagination)) {
                $auto_pagination =  AbstractPagination::create_from_first_response($this->payload);
                $this->pagination = $auto_pagination ? $auto_pagination : null;
                if (!is_null($this->pagination)) {
                    $this->pagination->set_limits($max_requests, $max_elements, $page_size, $request_delay);
                }
            }
            if (!is_null($this->pagination)) {
                $this->pagination->prepare_next_page_query($this->payload);
                Notice::trigger($this->pagination->get_status_report());
            }
        } while (!is_null($this->pagination) && $this->pagination->has_next());

        return $this;
    }

    /**
     * Debug info representation.
     *
     * @return array<string,mixed>
     */
    public function __debugInfo(): array
    {
        $new_info =   [];
        if (isset($this->payload)) {
            if ($this->payload->is_single_item()) {
                $new_info['payload'] = $this->payload->get_item();
            }
            if ($this->payload->is_collection()) {
                $new_info['payload'] = $this->payload->get_collection();
            }
        }
        if (isset($this->response)) {
            $new_info['response'] = $this->response->start_line;
        }

        $info = array_merge(
            parent::__debugInfo(),
            $new_info
        );

        unset($info['raw_body']);
        return $info;
    }

    /**
     * Decode the raw response body and consolidate it into `$this->payload`.
     *
     * @return static
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

            case 'application/ld+json':
            case 'application/json':
                $result = json_decode($this->response->raw_body, true);
                if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                    // Invalid JSON; fail hard or fallback?
                    throw new HTTPClientError("Invalid JSON in response body");
                }
                break;

            default:
                // Unsupported content type - leave $result empty or raw text?
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
     * Determine the content type of the current response.
     *
     * @return string
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
     * Convert an XML string into an associative array.
     *
     * @param SimpleXMLElement|string $xml_data
     * @return array<string,mixed>
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
