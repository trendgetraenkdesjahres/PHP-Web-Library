<?php

namespace Router;

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

class ResponseCreator
{
    /**
     * Get a response object based on the request.
     *
     * @param Request $request The request object.
     * @param string $responseType The type of response to create.
     *
     * @return Response The response object.
     */
    public static function get_response(Request $request, string $responseType = 'html'): Response
    {
        switch ($responseType) {
            case 'html':
                return new HTMLResponse($request);
            case 'json':
                return new JSONResponse($request);
            case 'cli':
                return new CLIResponse($request);
            default:
                throw new \InvalidArgumentException('Invalid response type');
        }
    }

    /**
     * Handle the response and send it to the client.
     *
     * @param Request $request The request object.
     * @param string $responseType The type of response to create.
     */
    public static function response(Request $request, string $responseType = 'html'): void
    {
        $response = self::get_response($request, $responseType);
        echo $response;
    }
}

class Response
{
    public ?string $resource = null;
    public ?int $code = null;
    public ?array $header = null;
    public ?string $body = null;
    public ?array $local_action = null;
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
            call_user_func([$this, 'set_code']);
            call_user_func([$this, 'set_local_documents']);
        }
        if ($request->get_method() == 'post') {
            call_user_func([$this, 'set_local_action']);
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
