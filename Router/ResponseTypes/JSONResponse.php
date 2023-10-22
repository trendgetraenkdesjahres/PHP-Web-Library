<?php

namespace Router;

/**
 * JSONResponse is a specialized class for handling JSON responses.
 */
class JSONResponse extends Response implements ResponseInterface
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
     * Set the response header for JSON responses.
     *
     * @return Response The response object.
     */
    public function set_header(): Response
    {
        $this->header = [
            'Content-Type: application/json'
        ];
        return $this;
    }

    /**
     * Set the response body for JSON responses.
     *
     * @param string $body The body content to set.
     *
     * @return Response The response object.
     */
    public function set_body(string $body = ''): Response
    {
        $this->body = "hello json";
        return $this;
    }
}
