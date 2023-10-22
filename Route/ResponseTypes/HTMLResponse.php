<?php

namespace Route;

class HTMLResponse extends Response implements ResponseInterface
{

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
