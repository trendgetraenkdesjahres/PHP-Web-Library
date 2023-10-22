<?php

namespace Route;

interface ResponseInterface
{
    public function __construct(Request $request);
    public function __toString(): string;
    public function set_code(): Response;
    public function set_body(): Response;
}

class Response
{
    public ?string $resource = null;
    public ?int $code = null;
    public ?array $header = null;
    public ?string $body = null;
    public ?array $local_action = null;
    public ?array $local_documents = null;

    public function __construct(Request $request)
    {
        $this->resource = $request->get_resource_path();

        if (get_class($request) != 'Route\CLIRequest') {
            call_user_func([$this, 'set_code']);
            call_user_func([$this, 'set_header']);
        }
        if ($request->get_resource_path()) {
            call_user_func([$this, 'set_code']);
            call_user_func([$this, 'set_local_documents']);
        }
        if ($request->get_method() == 'post') {
            call_user_func([$this, 'set_local_action']);
        }
        call_user_func([$this, 'set_body']);
    }


    public function __toString(): string
    {
        if ($this->header) {
            foreach ($this->header as $header) {
                header(
                    header: $header,
                    response_code: $this->code
                );
            }
        }
        return $this->body;
    }
}

foreach (glob(dirname(__FILE__) . "/ResponseTypes/*Response.php") as $file) {
    require_once $file;
}
