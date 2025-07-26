<?php

namespace PHP_Library\HTTP\HTTPClient\HTMLClient;

use DOMDocument;
use PHP_Library\HTTP\HTTPClient\HTTP1Client;
use PHP_Library\HTTP\HTTPMessage\HTTPHeader\HTTPRequestHeader;

class HTMLClient extends HTTP1Client
{
    public DOMDocument $html_doc;

    public function __construct(string $url, string $data = '', ?HTTPRequestHeader $header = null, string $agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36")
    {
        if(is_null($header)) {
            $header = new HTTPRequestHeader();
        }
        $header->accept = 'text/html';
        parent::__construct($url, 'GET',  $data, $header, $agent);
    }

    public function send(?string $method = null): static
    {
        parent::send($method);
        $encoding = $this->response->get_header_field('charset') ?? 'utf-8';
        $this->html_doc = new DOMDocument(encoding: $encoding);
        $this->html_doc->loadHTML($this->response->raw_body, LIBXML_NOERROR);
        return $this;
    }
}
