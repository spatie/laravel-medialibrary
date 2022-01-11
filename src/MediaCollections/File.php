<?php

namespace Spatie\MediaLibrary\MediaCollections;

class File implements \Stringable
{
    public static function createFromMedia($media)
    {
        return new static($media->file_name, $media->size, $media->mime_type);
    }

    public function __construct(
        public string $name,
        public int $size,
        public string $mimeType
    ) {
    }

    public function __toString(): string
    {
        return "name: {$this->name}, size: {$this->size}, mime: {$this->mimeType}";
    }
}
