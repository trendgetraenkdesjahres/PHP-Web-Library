<?php

namespace  PHP_Library\System;

use PHP_Library\Notices\Warning;

class FileHandle
{
    public string $path;
    public string $name;
    private bool $lock_file;

    private mixed $file_handle = null;
    private mixed $memory = null;

    public function __construct(string $path, bool $lock_file = true)
    {
        $this->path = realpath($path);
        $this->name = pathinfo($path, PATHINFO_BASENAME);
        $this->lock_file = $lock_file;
    }

    public function __destruct()
    {
        if ($this->file_handle) {
            $this->close_file();
        }
    }

    public function __toString()
    {
        return $this->path;
    }

    public function open_file(string $fopen_mode = 'r', bool $load_file = true, int $microseconds_freq = 100): FileHandle
    {
        if ($this->lock_file) {
            while (!@mkdir($this->path . ".lock")) {
                usleep($microseconds_freq);
                // TODO limit einstellen
            }
        }
        if (!$this->file_handle = fopen($this->path, $fopen_mode)) {
            Warning::trigger("Could not fopen(filename: '{$this->path}', mode: '$fopen_mode').");
            $this->close_file();
        }
        if ($load_file) {
            $this->memory = unserialize(
                data: stream_get_contents($this->file_handle),
                options: []
            );
        }
        return $this;
    }

    public function close_file(): FileHandle
    {
        if (get_resource_type($this->file_handle) == 'stream') {
            fclose($this->file_handle);
        }
        if ($this->lock_file) {
            @rmdir($this->path . ".lock");
        }
        return $this;
    }

    public function write_file(mixed $data): FileHandle
    {
        rewind($this->file_handle);
        if (!fwrite($this->file_handle, serialize($data))) {
            $type = get_resource_type($this->file_handle);
            Warning::trigger("Could not fwrite($type, \$data).");
        }
        $this->memory = $data;
        return $this;
    }

    public function create_file(bool $force = false): FileHandle
    {
        if ($force) {
            $stream = fopen($this->path, 'w');
        } else {
            $stream = @fopen($this->path, 'x');
        }
        if ($stream) {
            fclose($stream);
        }
        return $this;
    }

    public function get_memory()
    {
        return $this->memory;
    }

    public function get_change_time(): int
    {
        return filectime($this);
    }

    public function get_last_access_time(): int
    {
        return fileatime($this);
    }

    public function get_modification_time(): int
    {
        return filemtime($this);
    }
}
