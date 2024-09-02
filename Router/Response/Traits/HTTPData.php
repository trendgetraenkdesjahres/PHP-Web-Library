<?php

namespace  PHP_Library\Router\Response\Traits;

trait HTTPData
{
    public function articulate(): void
    {
        $size = filesize($this->path);
        $time = date('r', filemtime($this->path));

        $fm = @fopen($this->path, 'rb');
        if (!$fm) {
            header("HTTP/1.0 505 Internal server error");
            return;
        }
        $begin = 0;
        $end = $size;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin = intval($matches[0]);
                if (!empty($matches[1]))
                    $end = intval($matches[1]);
            }
        }

        if ($begin > 0 || $end < $size)
            header('HTTP/1.0 206 Partial Content');
        else
            header('HTTP/1.0 200 OK');

        header("Content-Type: $this->mime_type");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:' . ($end - $begin));
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Disposition: inline; filename='" . basename($this->path) . "'");
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: $time");
        header('Connection: close');

        $cur = $begin;
        fseek($fm, $begin, 0);

        while (!feof($fm) && $cur < $end && (connection_status() == 0)) {
            print fread($fm, min(1024 * 16, $end - $cur));
            $cur += 1024 * 16;
        }
    }
}
