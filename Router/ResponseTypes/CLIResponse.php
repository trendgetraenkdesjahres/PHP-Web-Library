<?php

namespace Router;

/**
 * CLIResponse is a specialized class for handling responses in the command-line interface (CLI).
 */
class CLIResponse extends Response implements ResponseInterface
{
    /**
     * Set the HTTP response code based on local documents.
     *
     * @return Response The response object.
     */
    public function set_code(): Response
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
     * @return Response The response object.
     */
    public function set_header(): Response
    {
        return $this;
    }

    /**
     * Set the response body for CLI output.
     *
     * @param string $body The body content to set.
     *
     * @return Response The response object.
     */
    public function set_body(string $body = ''): Response
    {
        $this->body = "hello cli\n\n";
        return $this;
    }
}
