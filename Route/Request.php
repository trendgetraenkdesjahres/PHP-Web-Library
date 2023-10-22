<?php

namespace Route;

interface RequestInterface
{
    public function response(): void;
    public function get_response(): Response;
    public function get_method(): string|null;
    public function get_data(): array|null;
    public function get_resource_path(): string|null;
}

class Request
{
    public static function get(): Request
    {
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            return new CLIRequest();
        }

        if (($_SERVER['REQUEST_METHOD'] == 'POST')) {
            if (
                isset($_SERVER['CONTENT_TYPE'])
                && is_int(strpos($_SERVER['CONTENT_TYPE'], 'application/json'))
            ) {
                return new JSONRequest(method: 'post');
            }

            if (
                isset($_SERVER['CONTENT_TYPE'])
                && is_int(strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data'))
            ) {
                return new DataRequest(method: 'post');
            }

            if (
                isset($_SERVER['CONTENT_TYPE'])
                && is_int(strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded'))
            ) {
                return new FormRequest(method: 'post');
            }
        }

        if (
            isset($_SERVER['HTTP_ACCEPT'])
            && is_int(strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))
        ) {
            $query = self::get_query_array($_SERVER["QUERY_STRING"]);
            return new HTMLRequest(
                method: 'get',
                resource_path: strtok($_SERVER["REQUEST_URI"], '?'),
                data: $query
            );
        }

        if (
            isset($_SERVER['HTTP_ACCEPT'])
            && is_int(strpos($_SERVER['HTTP_ACCEPT'], 'application/json'))
        ) {
            $query = self::get_query_array($_SERVER["QUERY_STRING"]);
            return new JSONRequest(
                method: 'get',
                resource_path: strtok($_SERVER["REQUEST_URI"], '?'),
                data: $query
            );
        }
    }

    public function get_method(): string|null
    {
        return $this->method;
    }
    public function get_data(): array|null
    {
        return $this->data;
    }
    public function get_resource_path(): string|null
    {
        return $this->resource_path;
    }

    public function get_response(): Response
    {
        $response = new HTMLResponse($this);
        return $response;
    }

    private static function get_query_array(string $query): array|null
    {
        $return = [];
        if (!$query) {
            return null;
        }
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

    public function __construct(public ?string $method = null, public ?string $resource_path = null, public ?array $data = null)
    {
    }

    public function response(): void
    {
        $reponse_method = 'get_response';
        if (method_exists($this, $reponse_method)) {
            echo call_user_func([$this, $reponse_method]);
        } else {
            $class_name = get_class($this);
            throw new \Exception("Public function '$reponse_method' for '$class_name' not defined.", 1);
        }
    }
}

foreach (glob(dirname(__FILE__) . "/RequestTypes/*Request.php") as $file) {
    require_once $file;
}
