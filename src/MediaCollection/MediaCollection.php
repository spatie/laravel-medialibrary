<?php

namespace Spatie\MediaLibrary\MediaCollection;

use Spatie\MediaLibrary\HasMedia\HasMedia;

class MediaCollection
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $diskName = '';

    /** @var callable */
    public $mediaConversionRegistrations;

    public function __construct(string $name)
    {
        $this->name = $name;

        $this->mediaConversionRegistrations = function() {};
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

    public function registerMediaConversions(callable $mediaConversionRegistrations)
    {
        $this->mediaConversionRegistrations = $mediaConversionRegistrations;
    }
}