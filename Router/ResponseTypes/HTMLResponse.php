<?php

namespace Router;

/**
 * HTMLResponse is a specialized class for handling HTML responses.
 */
class HTMLResponse extends Response implements ResponseInterface
{

    /**
     * Set the local documents based on the resource path.
     *
     * @return Response The response object.
     */
    protected function set_local_documents(): Response
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

    public function set_header(): Response
    {
        $this->header = [
            'Content-Type: text/html'
        ];
        return $this;
    }

    public function set_body(): Response
    {
        if ($this->local_documents) {
            ob_start();
            foreach ($this->local_documents as $document) {
                include $document;
            }
            $this->body = ob_get_clean();
        } else {
            $this->body = 'not found';
        }
        return $this;
    }
}
