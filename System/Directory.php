<?php

namespace  PHP_Library\System;

class Directory implements \Iterator
{
    public string $name;
    public string $path;
    protected array $files = [];
    protected array $folders = [];
    private bool $was_read = false;

    public static function open(string $path = '.'): self
    {
        return new Directory($path, null);
    }

    public function __construct(string $path, ?int $permissions = 0777)
    {
        $path = realpath($path);
        if (!$path) {
            throw new \Error("'{$path}' is not a valid path name.");
        }

        $this->path = $path;
        $this->name = pathinfo($path, PATHINFO_BASENAME);
        if ($permissions) {
            $this->mkdir(
                permissions: $permissions,
                throw_error: false
            );
        }
    }

    public function mkdir(int $permissions = 0777, bool $recursive = false, bool $throw_error = true): self
    {
        if (!@mkdir($this->path, $permissions, $recursive) && $throw_error) {
            throw new \Error("Could not create {$this->path}");
        }
        return $this;
    }

    public function get_size(): float
    {
        return (float) exec("du -s -m " . $this->path);
    }

    public function read(): static
    {
        foreach (scandir($this->path) as $item_name) {
            if ($item_name == '.' || $item_name == '..') {
                continue;
            }
            $this->add_item($item_name);
        }
        $this->was_read = true;
        return $this;
    }

    public function glob($pattern): array
    {
        if (is_int(strpos($pattern, DIRECTORY_SEPARATOR))) {
            throw new \Error("No sub directories!");
        }
        if (!$this->was_read) {
            $files = glob($this->path . '/' . $pattern);
            $this->add_item(...$files);
        }
        return $this->glob_items($pattern);
    }

    public function get_files(): array
    {
        return $this->files;
    }

    public function __toString(): string
    {
        return $this->path;
    }

    public function rewind(): void
    {
        reset($this->files);
    }

    public function current(): mixed
    {
        return current($this->files);
    }
    public function key(): mixed
    {
        return key($this->files);
    }
    public function next(): void
    {
        next($this->files);
    }
    public function valid(): bool
    {
        return key($this->files) !== null;
    }

    protected function glob_items($pattern): array
    {
        $matches = [];
        foreach ($this->files as $file_name => $file) {
            if (fnmatch($pattern, $file_name)) {
                $matches[$file_name] = $file;
            }
        }
        foreach ($this->folders as $folder_name => $folder) {
            if (fnmatch($pattern, $folder_name)) {
                $matches[$folder_name] = $folder;
            }
        }
        return $matches;
    }

    protected function add_item(string|FileHandle|Directory ...$item): static
    {
        foreach ($item as $item) {
            if (is_string($item)) {
                $item = static::create_item($item);
            }
            if ($item instanceof FileHandle) {
                $this->files[$item->name] = $item;
            }
            if ($item instanceof Directory) {
                $this->folders[$item->name] = $item;
            }
        }
        return $this;
    }

    protected static function create_item($path): FileHandle|Directory
    {
        if (is_file($path)) {
            return new FileHandle($path);
        }
        return Directory::open($path);
    }
}
