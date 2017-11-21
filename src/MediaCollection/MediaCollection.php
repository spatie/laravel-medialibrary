<?php

namespace Spatie\MediaLibrary\MediaCollection;

class MediaCollection
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $diskName = '';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create($name)
    {
        return new static($name);
    }

    public function disk(string $diskName)
    {
        $this->diskName = $diskName;

        return $this;
    }
}