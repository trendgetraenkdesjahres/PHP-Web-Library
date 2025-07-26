<?php

namespace PHP_Library\HTTP\HTTPClient;

use PHP_Library\HTTP\HTTP1Request\HTTP1Request;
use PHP_Library\HTTP\HTTPClient\Auth\AbstractAuth;
use PHP_Library\HTTP\HTTPResponse\HTTPResponse;
use PHP_Library\HTTP\HTTPClient\Error\HTTPClientError;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader\HTTPHeader;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader\HTTPRequestHeader;

/**
 * HTTP/1.x client using cURL.
 *
 * Maintains persistent client state such as cookies, auth, headers, and query params.
 * Builds and sends HTTP requests via HTTP1Request.
 */
class HTTP1Client extends HTTP1Request
{
    /** @var string HTTP request hostname */
    protected string $host;

    /** @var string URL scheme, e.g., http or https */
    protected string $scheme;

    /** @var int|null Optional port number */
    protected ?int $port;

    /** @var string|null Base prefix path for URL construction */
    protected ?string $prefix_path = null;

    /** @var string Request path (overrides or appends to prefix path) */
    protected string $path = '/';

    /** @var array Associative query parameter map */
    protected array $query = [];

    /** @var string URL fragment (without #) */
    protected string $fragment = '';

    /** @var string|null User-Agent string for the request */
    protected ?string $agent = null;

    /** @var string|null File path for storing and reading cookies */
    protected ?string $cookie_file = null;

    /** @var array Associative array of request body data */
    protected array $request_data = [];

    /** @var AbstractAuth|null Auth strategy for headers or query signing */
    protected ?AbstractAuth $auth = null;

    /** @var \CurlHandle Active cURL handle */
    protected \CurlHandle $handle;

    /** @var HTTPResponse Response object for the last request sent */
    public HTTPResponse $response;

    /**
     * Constructor builds a client from a full URL and method.
     *
     * @param string $url Full request URL
     * @param string $default_method HTTP method (GET, POST, etc.)
     * @param string|array $data Request body as string or array
     * @param HTTPRequestHeader|null $header Optional headers
     * @param string $agent User-Agent string
     * @param string $http_version HTTP version string, default 'HTTP/1.1'
     * @param AbstractAuth|null $auth Optional auth object
     */
    public function __construct(string $url, string $default_method, string|array $data = '', ?HTTPRequestHeader $header = null, string $agent = '', string $http_version = 'HTTP/1.1', ?AbstractAuth $auth = null)
    {
        if (!$agent) {
            $agent = 'curl/' . curl_version()['version'];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new HTTPClientError("$url invalid.");
        }

        $resource = parse_url($url);
        $this->host = $resource['host'];
        $this->scheme = $resource['scheme'] ?? 'http';
        $this->port = $resource['port'] ?? null;
        $this->prefix_path = $resource['path'] ?? '/';
        $this->fragment = $resource['fragment'] ?? '';

        if (isset($resource['query'])) {
            parse_str($resource['query'], $this->query);
        }

        $this->agent = $agent;

        if (is_array($data)) {
            $this->request_data = $data;
            $data = '';
        }

        if ($auth) {
            $this->auth = $auth;
            $this->auth->set_default_host($url);
        }

        $request_uri = $this->get_current_request_url();

        parent::__construct($default_method, $request_uri, $http_version, $header, $data);
    }

    /**
     * Returns debug data for this client.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        $info = [
            'agent' => $this->agent,
            'request_url' => $this->get_current_request_url(),
            'prefix_path' => $this->prefix_path,
            'path' => $this->path,
        ];

        if (isset($this->response)) {
            $info['response'] = "{$this->response->start_line}\n{$this->response->raw_body}";
        }

        if ($this->request_data) {
            $info['request_data'] = $this->request_data;
        }

        if ($this->cookie_file) {
            $info['cookie_file'] = $this->cookie_file;
        }

        $info = array_merge(parent::__debugInfo(), $info);
        unset($info['raw_body']);

        return $info;
    }

    /**
     * Sets a prefix path to prepend to the request path unless overridden.
     *
     * @param string $prefix_path Leading slash optional
     * @return static
     */
    public function set_prefix_path(string $prefix_path): static
    {
        $this->prefix_path = $prefix_path === '' ? null : ($prefix_path[0] === '/' ? $prefix_path : "/$prefix_path");
        return $this;
    }

    /**
     * Sets the request path.
     * Leading "/" overrides the prefix path.
     *
     * @param string $path
     * @return static
     */
    public function set_path(string $path): static
    {
        $this->path = $path === '' ? '/' : $path;
        return $this;
    }

    /**
     * Sets full target URL (path + optional query params).
     *
     * @param string $target
     * @param array|null $query_parameters
     * @return static
     */
    public function set_target_url(string $target = '', ?array $query_parameters = null): static
    {
        $this->set_path($target);
        if ($query_parameters !== null) {
            $this->set_query($query_parameters);
        }
        return $this;
    }

    /**
     * Replaces query parameters.
     *
     * @param array $query_data
     * @return static
     */
    public function set_query(array $query_data): static
    {
        $this->query = $query_data;
        return $this;
    }

    /**
     * Adds or updates query parameters.
     *
     * @param array $query_data
     * @return static
     */
    public function add_to_query(array $query_data): static
    {
        $this->query = array_merge($this->query, $query_data);
        return $this;
    }

    /**
     * Returns the composed request URL.
     *
     * @return string
     */
    public function get_current_request_url(): string
    {
        if ($this->auth) {
            $this->add_to_query($this->auth->get_query_params());
            if(!isset($this->header)) {
                $this->header = new HTTPRequestHeader();
            }
            foreach ($this->auth->get_header_fields() as $key => $value) {
                $this->header->set($key, $value);
            }
        }

        $composed_path = $this->compose_full_path();

        return static::build_url(
            $this->scheme,
            $this->host,
            $this->port,
            $composed_path,
            $this->query,
            $this->fragment
        );
    }

    /**
     * Returns just the query string.
     *
     * @return string
     */
    public function get_query_string(): string
    {
        return http_build_query($this->query);
    }

    /**
     * Sends the HTTP request using cURL and captures the response.
     *
     * @param string|null $method Override method
     * @return static
     * @throws HTTPClientError
     */
    public function send(?string $method = null): static
    {
        $method = $method ?? $this->method;
        $this->handle = $this->create_curl_handle();

        switch ($method) {
            case 'GET':
            case 'HEAD':
                curl_setopt($this->handle, CURLOPT_HTTPGET, true);
                break;

            case 'POST':
                curl_setopt($this->handle, CURLOPT_POST, true);
                $data = $this->raw_body ?: $this->get_request_data();
                if ($data) {
                    curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
                }
                break;

            default:
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
                $data = $this->raw_body ?: $this->get_request_data();
                if ($data) {
                    curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
                }
                break;
        }

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_HEADER, true);

        $result = curl_exec($this->handle);
        if ($result === false) {
            throw new HTTPClientError(curl_error($this->handle));
        }

        $header_size = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
        $this->response = HTTPResponse::from_raw(
            substr($result, 0, $header_size) . PHP_EOL . substr($result, $header_size)
        );

        return $this;
    }

    /**
     * Get info from the active cURL handle.
     *
     * @param int $opt
     * @return mixed
     */
    public function get_curl_info(int $opt)
    {
        return curl_getinfo($this->handle, $opt);
    }

    /**
     * Set a cURL option.
     *
     * @param int $curl_option
     * @param mixed $value
     * @return static
     */
    public function set_curl_option(int $curl_option, mixed $value): static
    {
        if (!isset($this->handle)) {
            $this->handle = $this->create_curl_handle();
        }

        curl_setopt($this->handle, $curl_option, $value);
        return $this;
    }

    /**
     * Build cURL handle with current request parameters.
     *
     * @return \CurlHandle
     */
    protected function create_curl_handle(): \CurlHandle
    {
        $opts = [
            CURLOPT_URL => $this->get_current_request_url(),
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_HTTPHEADER => $this->header ? $this->header->to_lines() : [],
        ];

        $this->raw_body = $this->raw_body ?: $this->get_request_data();

        if (!in_array($this->method, ['GET', 'HEAD']) && $this->raw_body) {
            $opts[CURLOPT_POSTFIELDS] = $this->raw_body;
            $this->set_header_field('Content-Length', strlen($this->raw_body));
        }

        if ($this->agent) {
            $opts[CURLOPT_USERAGENT] = $this->agent;
        }

        if ($this->cookie_file) {
            $opts[CURLOPT_COOKIEFILE] = $this->cookie_file;
            $opts[CURLOPT_COOKIEJAR] = $this->cookie_file;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        return $ch;
    }

    /**
     * Compose the path by combining prefix and path rules.
     *
     * @return string
     */
    protected function compose_full_path(): string
    {
        $prefix = $this->prefix_path ?? '';
        $path = $this->path ?? '/';

        if ($path === '') {
            return '/';
        }

        if ($path[0] === '/') {
            return $path;
        }

        $prefix = rtrim($prefix, '/');
        $path = ltrim($path, '/');

        return $prefix . '/' . $path;
    }

    /**
     * Encode request data based on content-type.
     *
     * @return string
     */
    protected function get_request_data(): string
    {
        $data_type = strtolower($this->get_header_field('content-type') ?? '');

        return match ($data_type) {
            'application/json' => json_encode($this->request_data, JSON_THROW_ON_ERROR),
            'multipart/form-data' => static::build_multipart($this->request_data, $data_type),
            'application/x-www-form-urlencoded' => http_build_query($this->request_data, '', '&', PHP_QUERY_RFC1738),
            default => http_build_query($this->request_data, '', '&', PHP_QUERY_RFC3986),
        };
    }

    /**
     * Factory method to create a client from a request object.
     *
     * @param HTTP1Request $request
     * @param string $hostname
     * @param string $scheme
     * @param int|null $port
     * @return static
     */
    public static function from_request(HTTP1Request $request, string $hostname, string $scheme = 'https', ?int $port = null): static
    {
        $destination = "{$scheme}://{$hostname}" . (is_int($port) ? ":{$port}" : '') . "/" . ltrim($request->request_uri, "/");

        return new static($destination, $request->method, $request->raw_body, $request->header);
    }

    /**
     * Validate scheme.
     */
    protected static function validate_scheme(string $scheme): bool
    {
        return (bool) filter_var($scheme, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^https?$/i']]);
    }

    /**
     * Validate host.
     */
    protected static function validate_host(string $host): bool
    {
        return (bool) (filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || filter_var($host, FILTER_VALIDATE_IP));
    }

    /**
     * Validate port.
     */
    protected static function validate_port(?int $port): bool
    {
        return $port === null || ($port >= 1 && $port <= 65535);
    }

    /**
     * Validate path.
     */
    protected static function validate_path(string $path): bool
    {
        return (bool) preg_match('#^/[^\s]*$#', $path);
    }

    /**
     * Validate query string.
     */
    protected static function validate_query(string $query): bool
    {
        return $query === '' || (bool) preg_match('/^[\x20-\x7E]+$/', $query);
    }

    /**
     * Validate URL fragment.
     */
    protected static function validate_fragment(string $fragment): bool
    {
        return $fragment === '' || (bool) preg_match('/^[\x21-\x7E]+$/', $fragment);
    }

    /**
     * Build URL from components.
     */
    public static function build_url(string $scheme, string $host, ?int $port = null, string $path = '/', string|array $query = [], string $fragment = ''): string
    {
        if (is_array($query)) {
            $query = http_build_query($query);
        }

        if (!static::validate_scheme($scheme)) {
            throw new \InvalidArgumentException("Invalid scheme: {$scheme}");
        }
        if (!static::validate_host($host)) {
            throw new \InvalidArgumentException("Invalid host: {$host}");
        }
        if (!static::validate_port($port)) {
            throw new \InvalidArgumentException("Invalid port: {$port}");
        }
        if (!static::validate_path($path)) {
            throw new \InvalidArgumentException("Invalid path: {$path}");
        }
        if (!static::validate_query($query)) {
            throw new \InvalidArgumentException("Invalid query string: {$query}");
        }
        if (!static::validate_fragment($fragment)) {
            throw new \InvalidArgumentException("Invalid fragment: {$fragment}");
        }

        return vsprintf('%s://%s%s%s%s%s', [
            $scheme,
            $host,
            $port ? ":{$port}" : '',
            $path ?: '/',
            $query ? "?{$query}" : '',
            $fragment ? "#{$fragment}" : '',
        ]);
    }
}
