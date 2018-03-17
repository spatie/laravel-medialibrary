<?php

namespace Spatie\MediaLibrary;

class File
{
    /** @var string */
    public $name;

    /** @var int */
    public $size;

    /** @var string */
    public $mimeType;

    public static function createFromMedia($media)
    {
        return new static($media->file_name, $media->size, $media->mime_type);
    }

    public function __construct(string $name, int $size, string $mimeType)
    {
        $this->name = $name;

        $this->size = $size;

        $this->mimeType = $mimeType;
    }

    public function __toString()
    {
        return "name: {$this->name}, size: {$this->size}, mime: {$this->mimeType}";
    }
}
