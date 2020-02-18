<?php

namespace Spatie\Medialibrary;

use Spatie\Medialibrary\Models\Media;

class File
{
    public string $path;

    public string $extension;

    public string $name;

    public int $size;

    public string $mimeType;

    public static function createFromMedia(Media $media): self
    {
        return new static($media->file_name, $media->size, $media->mime_type);
    }

    public function __construct(string $path, int $size, string $mimeType)
    {
        $this->path = $path;

        $this->name = pathinfo($this->path, PATHINFO_FILENAME);

        $this->extension = pathinfo($this->path, PATHINFO_EXTENSION);

        $this->size = $size;

        $this->mimeType = $mimeType;
    }

    public function __toString()
    {
        return "name: {$this->path}, size: {$this->size}, mime: {$this->mimeType}";
    }
}
