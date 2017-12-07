<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

class ResponsiveImages extends Collection
{
    public static function createForMedia(Media $media, string $conversionName = '')
    {
        $generatedFor = $conversionName === ''
            ? 'medialibrary_original'
            : $conversionName;

        $items = collect($media->responsive_images[$generatedFor]['urls'] ?? [])
            ->map(function (string $fileName) use ($media) {
                return new ResponsiveImage($fileName, $media);
            })
            ->filter(function (ResponsiveImage $responsiveImage) use ($generatedFor) {
                return $responsiveImage->generatedFor() === $generatedFor;
            })->toArray();

        return new static($items);
    }

    public function getUrls(): array
    {
        return $this
            ->map(function (ResponsiveImage $responsiveImage) {
                return $responsiveImage->url();
            })
            ->values()
            ->toArray();
    }

    public function getSrcset(): string
    {
        return $this
            ->map(function (ResponsiveImage $responsiveImage) {
                return "{$responsiveImage->url()} {$responsiveImage->width()}w";
            })
            ->implode(', ');
    }
}
