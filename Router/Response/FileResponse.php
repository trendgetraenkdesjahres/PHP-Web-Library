<?php

namespace  PHP_Library\Router\Response;

use PHP_Library\Router\Response\Traits\HTTPData;

/**
 * HTMLResponse is a specialized class for handling HTML responses.
 */
class FileResponse extends AbstractResponse
{
    use HTTPData;

    public string $mime_type;
    public string $path;

    public function set_body(mixed $file_info): static
    {
        $file_info = explode(" ", $file_info);
        $this->path = $file_info[1];
        $this->mime_type = $file_info[0];
        return $this;
    }
}
