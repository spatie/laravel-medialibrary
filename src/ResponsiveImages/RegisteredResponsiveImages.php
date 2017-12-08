<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

class RegisteredResponsiveImages
{
    /** Spatie\Medialibrary\Media */
    protected $media;

    /** Illuminate\Support\Collection */
    public $files;

    /** string */
    protected $generatedFor;

    public function __construct(Media $media, string $conversionName = '')
    {
        $this->media = $media;

        $this->generatedFor = $conversionName === ''
            ? 'medialibrary_original'
            : $conversionName;

        $this->files = collect($media->responsive_images[$this->generatedFor]['urls'] ?? [])
            ->map(function (string $fileName) use ($media) {
                return new ResponsiveImage($fileName, $media);
            })
            ->filter(function (ResponsiveImage $responsiveImage) {
                return $responsiveImage->generatedFor() === $this->generatedFor;
            });
    }

    public function getUrls(): array
    {
        return $this->files
            ->map(function (ResponsiveImage $responsiveImage) {
                return $responsiveImage->url();
            })
            ->values()
            ->toArray();
    }

    public function getSrcset(): string
    {
        $filesSrcset = $this->files
            ->map(function (ResponsiveImage $responsiveImage) {
                return "{$responsiveImage->url()} {$responsiveImage->width()}w";
            })
            ->implode(', ');
        return $filesSrcset . ', ' . $this->getPlaceholderSvg() . ' 32w';
    }

    public function getPlaceholderSvg(): string
    {
        return $this->media->responsive_images[$this->generatedFor]['base64svg'];
    }
}
