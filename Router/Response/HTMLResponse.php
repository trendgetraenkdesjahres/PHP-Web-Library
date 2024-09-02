<?php

namespace  PHP_Library\Router\Response;

use PHP_Library\Router\Response\Traits\HTTPText;

/**
 * HTMLResponse is a specialized class for handling HTML responses.
 */
class HTMLResponse extends AbstractResponse
{
    use HTTPText;
    public array $header =  ['Content-Type: text/html'];
    public string $html_head;

    public function set_body(mixed $html_body_content): static
    {
        $html_head = isset($this->html_head) ? "<head>\n$this->html_head\n</head>\n" : '';
        $this->body = "<!DOCTYPE html>\n<html>\n{$html_head}<body>\n{$html_body_content}\n</body>\n</html>";
        return $this;
    }
}
