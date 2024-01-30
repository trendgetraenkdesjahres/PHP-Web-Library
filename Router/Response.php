<?php

namespace Library\Router;

/**
 * ResponseInterface defines the methods that should be implemented by response classes.
 */
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
    public ?array $local_documents = null;

    /**
     * Constructor to initialize a response based on the request.
     *
     * @param Request $request The request for which the response is generated.
     */
    public function __construct(Request $request)
    {
        $this->resource = $request->get_resource_path();

        if (get_class($request) != 'Route\CLIRequest') {
            call_user_func([$this, 'set_code']);
            call_user_func([$this, 'set_header']);
        }
        if ($request->get_resource_path()) {
            try {
                call_user_func([$this, 'set_body_source']);
            } catch (\Throwable $e) {
                throw new \Error(
                    get_class($this) . "->set_body_source() must be declared to handle a '" . get_class($request) . "'."
                );
            }
        }
        call_user_func([$this, 'set_body']);
    }

    /**
     * Convert the response to a string. And sets the header as this is the last moment before returning content.
     *
     * @return string The response as a string.
     */
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

// Include response type classes.
foreach (glob(dirname(__FILE__) . "/ResponseTypes/*Response.php") as $file) {
    require_once $file;
}
