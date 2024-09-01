<?php

namespace  PHP_Library\Router;


abstract class Response
{
    abstract public function set_body(mixed $content): static;

    public ?string $resource = null;
    public ?int $code = null;
    public array $header;
    public ?string $body = null;
    public ?array $local_documents = null;

    public function add_header(string ...$header): static
    {
        foreach ($header as $header_line) {
            array_push($this->header, $header_line);
        }
        return $this;
    }

    /**
     * Constructor to initialize a response based on the request.
     *
     * @param Request $request The request for which the response is generated.
     */
    public function __construct(mixed $content, int $code)
    {
        $this->set_body($content);
        $this->code = $code;
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
