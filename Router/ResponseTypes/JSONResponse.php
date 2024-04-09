<?php

namespace  PHP_Library\Router\ResponseTypes;

/**
 * JSONResponse is a specialized class for handling JSON responses.
 */
class JSONResponse extends Response implements ResponseInterface
{

    /**
     * Set the local documents based on the resource path.
     *
     * @return Response The response object.
     */
    protected function set_body_source(): Response
    {
        if ($this->resource === '/' | $this->resource === '') {
            $files  = glob("content/*.{php,html}", GLOB_BRACE);
        } else {
            $files  = glob("content" . $this->resource . "/*.{php,html}", GLOB_BRACE);
        }
        if ($files) {
            $this->local_documents = $files;
        }
        return $this;
    }

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
        if ($this->local_documents) {
            ob_start();
            foreach ($this->local_documents as $document) {
                include $document;
            }
            $body = ob_get_clean();
        } else {
            $body = 'not found';
        }
        $this->body = json_encode($body);
        return $this;
    }
}