<?php

namespace  PHP_Library\Router\Response;

use PHP_Library\Router\Response\Traits\CLIText;

/**
 * CLIResponse is a specialized class for handling responses in the command-line interface (CLI).
 */
class CLIResponse extends AbstractResponse
{
    use CLIText;
    /**
     * Set the HTTP response code based on local documents.
     *
     * @return AbstractResponse The response object.
     */
    public function set_code(): static
    {
        if ($this->local_documents) {
            $this->code = 200;
        } else {
            $this->code = 404;
        }
        return $this;
    }

    /**
     * Set the HTTP response code based on local documents.
     *
     * @return static The response object.
     */
    public function set_header(): static
    {
        return $this;
    }

    /**
     * Set the response body for CLI output.
     *
     * @param string $body The body content to set.
     *
     * @return static The response object.
     */
    public function set_body(string $body = ''): static
    {
        $this->body = "hello cli\n\n";
        return $this;
    }
}
