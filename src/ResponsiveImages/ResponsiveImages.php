<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

class ResponsiveImages extends Collection
{
    /** \Spatie\MediaLibrary\Media */
    protected $media;

    public static function createForMedia(Media $media)
    {
        $items = collect($media->responsive_images)
        ->map(function (string $fileName) use ($media) {
            return new ResponsiveImage($fileName, $media);
        })->toArray();

        return new static($items);
    }
}
