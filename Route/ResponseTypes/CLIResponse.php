<?php

namespace Route;

class CLIResponse extends Response implements ResponseInterface
{
    public function set_code(): Response
    {
        if ($this->local_documents) {
            $this->code = 200;
        } else {
            $this->code = 404;
        }
        return $this;
    }

    public function set_body(string $body = ''): Response
    {
        $this->body = "hello cli";
        return $this;
    }
}
