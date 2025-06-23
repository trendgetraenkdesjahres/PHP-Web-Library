<?php

namespace PHP_Library\HTTP\HTTPClient;

use CurlHandle;
use PHP_Library\HTTP\HTTP1Request\HTTP1Request;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader;
use PHP_Library\HTTP\HTTPResponse\HTTPResponse;

class HTTP1Client extends HTTP1Request
{
    protected string $host;
    protected string $scheme;
    protected ?int $port;
    protected string $path;
    protected array|string $query;
    protected string $fragment;
    protected ?string $agent = null;
    protected ?string $cookie_file = null;
    protected array $request_data = [];

    public HTTPResponse $response;

    public function __construct(string $url, string $default_method, string|array $data = '', ?HTTPHeader $header = null, string $agent = "", string $http_version = "HTTP/1.1")
    {
        if (!$agent) {
            $agent = "curl/" . curl_version()['version'];
        }
        if (! preg_match('/^.{3,}:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Error("$url invalid.");
        }
        $resource = parse_url($url);
        $this->host = $resource['host'];
        $this->scheme = $resource['scheme'] ?? 'http';
        $this->port = $resource['port'] ?? null;
        $this->path = $resource['path'] ?? '/';
        $this->query = $resource['query'] ?? '';
        $this->fragment = $resource['fragment'] ?? '';
        $this->agent = $agent;
        $request_uri = $this->path . ($this->query ?? "{?$this->query}") . ($this->fragment ?? "{#$this->fragment}");
        if (is_array($data)) {
            $this->request_data = $data;
            $data = '';
        }
        parent::__construct($default_method, $request_uri, $http_version, $header, $data);
    }

    public function __debugInfo()
    {
        $info = [
            'agent' => $this->agent,
            'request_url' => static::build_url(
                $this->scheme,
                $this->host,
                $this->port,
                $this->path,
                $this->query,
                $this->fragment
            )
        ];
        if ($this->response) {
            $info['response'] = "{$this->response->start_line}\n{$this->response->raw_body}";
        }
        if ($this->request_data) {
            $info['request_data'] = $this->request_data;
        }
        if ($this->cookie_file) {
            $info['cookie_file'] = $this->cookie_file;
        }
        $info =  array_merge(parent::__debugInfo(), $info);
        unset($info['raw_body']);
        return $info;
    }

    public function set_path(string $path): static
    {
        $this->path = strpos($path, '/') === 0 ? $path : "/$path";
        return $this;
    }

    public function set_query(array $query_data): static
    {
        $this->query = $query_data;
        return $this;
    }

    public function add_to_query(array $query_data): static
    {
        if (is_array($this->query)) {
            $this->query = array_merge($this->query, $query_data);
            return $this;
        }
        $this->query = $this->query . "&" . http_build_query($query_data);
        return $this;
    }

    public function set_target_url(string $target = '', ?array $query_parameters = null): static
    {
        $this->set_path($target);
        if (!is_null($query_parameters)) {
            $this->set_query($query_parameters);
        }
        return $this;
    }

    public function send(?string $method = null): static
    {
        if (is_null($method)) {
            $method = $this->method;
        }
        $handle = $this->create_curl_handle();
        switch ($method) {
            case 'GET':
                curl_setopt($handle, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $this->get_request_data());
                break;
            default:
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
                $data = $this->get_request_data();
                if ($data) {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                }
                break;
        }
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, true);
        $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $result = curl_exec($handle);
        if (false === $result) {
            throw new \Error(curl_error($handle));
        }
        $this->response = HTTPResponse::from_raw(
            substr($result, 0, $header_size) . PHP_EOL . substr($result, $header_size)
        );
        return $this;
    }

    protected function create_curl_handle(): CurlHandle
    {
        $url = static::build_url(
            $this->scheme,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment
        );

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_HTTPHEADER => $this->header ? $this->header->to_array() : [],
        ];

        if ($this->method == "POST" && $this->raw_body) {
            $opts[CURLOPT_POSTFIELDS] = $this->raw_body;
        }
        if ($this->agent) {
            $opts[CURLOPT_USERAGENT] = $this->agent;
        }
        if ($this->cookie_file) {
            $opts[CURLOPT_COOKIEFILE] = $this->cookie_file;
            $opts[CURLOPT_COOKIEJAR]  = $this->cookie_file;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        return $ch;
    }


    static public function build_url(string $scheme, string $host, ?int $port = null, string $path = '/', string|array $query = [], string $fragment = ''): string
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
            $port     ? ":{$port}" : '',
            $path     ?: '/',
            $query    ? "?{$query}"      : '',
            $fragment ? "#{$fragment}"   : '',
        ]);
    }

    protected static function validate_scheme(string $scheme): bool
    {
        return (bool) filter_var(
            $scheme,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^https?$/i']]
        );
    }

    protected static function validate_host(string $host): bool
    {
        $isDomain = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        $isIp     = filter_var($host, FILTER_VALIDATE_IP);
        return (bool) ($isDomain || $isIp);
    }

    protected static function validate_port(?int $port): bool
    {
        if ($port === null) {
            return true;
        }
        return (bool) filter_var(
            $port,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 65535]]
        );
    }

    protected static function validate_path(string $path): bool
    {
        return (bool) filter_var(
            $path,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '#^/[^\s]*$#']]
        );
    }

    protected static function validate_query(string $query): bool
    {
        if ($query === '') {
            return true;
        }
        return (bool) filter_var(
            $query,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^[\x20-\x7E]+$/']]
        );
    }

    protected static function validate_fragment(string $fragment): bool
    {
        if ($fragment === '') {
            return true;
        }
        return (bool) filter_var(
            $fragment,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^[\x21-\x7E]+$/']]
        );
    }

    protected function get_request_data(): string
    {
        return http_build_query($this->request_data ?? $this->raw_body);
    }
}
