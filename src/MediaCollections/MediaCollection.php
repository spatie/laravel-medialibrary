<?php

namespace Spatie\MediaLibrary\MediaCollections;

use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class MediaCollection
{
    use Macroable;

    public string $diskName = '';

    public string $conversionsDiskName = '';

    /** @var callable */
    public $mediaConversionRegistrations;

    public bool $generateResponsiveImages = false;

    /** @var callable */
    public $acceptsFile;

    public array $acceptsMimeTypes = [];

    /** @var bool|int */
    public $collectionSizeLimit = false;

    public bool $singleFile = false;

    /** @var array<string, string> */
    public array $fallbackUrls = [];

    /** @var array<string, string> */
    public array $fallbackPaths = [];

    public function __construct(
        public string $name
    ) {
        $this->mediaConversionRegistrations = function () {};

        $this->acceptsFile = fn () => true;
    }

    public static function create($name): self
    {
        return new static($name);
    }

    /**
     * @return $this
     */
    public function useDisk(string $diskName): self
    {
        $this->diskName = $diskName;

        return $this;
    }

    /**
     * @return $this
     */
    public function storeConversionsOnDisk(string $conversionsDiskName): self
    {
        $this->conversionsDiskName = $conversionsDiskName;

        return $this;
    }

    /**
     * @return $this
     */
    public function acceptsFile(callable $acceptsFile): self
    {
        $this->acceptsFile = $acceptsFile;

        return $this;
    }

    /**
     * @return $this
     */
    public function acceptsMimeTypes(array $mimeTypes): self
    {
        $this->acceptsMimeTypes = $mimeTypes;

        return $this;
    }

    public function singleFile(): self
    {
        return $this->onlyKeepLatest(1);
    }

    /**
     * @return $this
     */
    public function onlyKeepLatest(int $maximumNumberOfItemsInCollection): self
    {
        if ($maximumNumberOfItemsInCollection < 1) {
            throw new InvalidArgumentException("You should pass a value higher than 0. `{$maximumNumberOfItemsInCollection}` given.");
        }

        $this->singleFile = ($maximumNumberOfItemsInCollection === 1);

        $this->collectionSizeLimit = $maximumNumberOfItemsInCollection;

        return $this;
    }

    public function registerMediaConversions(callable $mediaConversionRegistrations): void
    {
        $this->mediaConversionRegistrations = $mediaConversionRegistrations;
    }

    /**
     * @return $this
     */
    public function useFallbackUrl(string $url, string $conversionName = ''): self
    {
        if ($conversionName === '') {
            $conversionName = 'default';
        }

        $this->fallbackUrls[$conversionName] = $url;

        return $this;
    }

    /**
     * @return $this
     */
    public function useFallbackPath(string $path, string $conversionName = ''): self
    {
        if ($conversionName === '') {
            $conversionName = 'default';
        }

        $this->fallbackPaths[$conversionName] = $path;

        return $this;
    }

    /**
     * @return $this
     */
    public function withResponsiveImages(): self
    {
        $this->generateResponsiveImages = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withResponsiveImagesIf($condition): self
    {
        $this->generateResponsiveImages = (bool) (is_callable($condition) ? $condition() : $condition);

        return $this;
    }
}
