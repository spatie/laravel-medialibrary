<?php

namespace Spatie\MediaLibrary\MediaCollection;

use Illuminate\Support\Traits\Macroable;

class MediaCollection
{
    use Macroable;

    /** @var string */
    public $name = '';

    /** @var string */
    public $diskName = '';

    /** @var callable */
    public $mediaConversionRegistrations;

    /** @var callable */
    public $acceptsFile;

    /** @var bool */
    public $singleFile = false;

    /** @var string */
    public $fallbackUrl = '';

    /** @var string */
    public $fallbackPath = '';

    public function __construct(string $name)
    {
        $this->name = $name;

        $this->mediaConversionRegistrations = function () {
        };

        $this->acceptsFile = function () {
            return true;
        };
    }

    public static function create($name)
    {
        return new static($name);
    }

    public function useDisk(string $diskName): self
    {
        $this->diskName = $diskName;

        return $this;
    }

    public function acceptsFile(callable $acceptsFile): self
    {
        $this->acceptsFile = $acceptsFile;

        return $this;
    }

    public function singleFile(): self
    {
        $this->singleFile = true;

        return $this;
    }

    public function registerMediaConversions(callable $mediaConversionRegistrations)
    {
        $this->mediaConversionRegistrations = $mediaConversionRegistrations;
    }

    public function useFallbackUrl(string $url): self
    {
        $this->fallbackUrl = $url;

        return $this;
    }

    public function useFallbackPath(string $path): self
    {
        $this->fallbackPath = $path;

        return $this;
    }
}
