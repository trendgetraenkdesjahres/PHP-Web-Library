<?php

namespace  PHP_Library\Router\ResponseTraits;

trait HTTPData
{
    public function articulate(): void
    {
        $file_size = filesize($this->path);
        $file_date = date('r', filemtime($this->path));

        $file = @fopen($this->path, 'rb');
        if (!$file) {
            header("HTTP/1.0 505 Internal server error");
            return;
        }

        $file_position = 0;
        $file_end = $file_size;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $file_position = intval($matches[0]);
                if (!empty($matches[1]))
                    $file_end = intval($matches[1]);
            }
        }

        if ($file_position > 0 || $file_end < $file_size)
            header('HTTP/1.0 206 Partial Content');
        else
            header('HTTP/1.0 200 OK');

        header("Content-Type: $this->mime_type");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:' . ($file_end - $file_position));
        header("Content-Range: bytes $file_position-$file_end/$file_size");
        header("Content-Disposition: inline; filename='" . basename($this->path) . "'");
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: $file_date");
        header('Connection: close');

        fseek($file, $file_position, 0);
        while (!feof($file) && $file_position < $file_end && (connection_status() == 0)) {
            print fread($file, min(1024 * 16, $file_end - $file_position));
            $file_position += 1024 * 16;
        }
    }
}
