<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

class RegisteredResponsiveImages extends Collection
{
    /** \Spatie\MediaLibrary\Media */
    protected $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
  
        $this->items = collect($media->responsive_images)->map(function (string $fileName) use ($media) {
            return new ResponsiveImage($fileName, $media);
        });
    }

    public function register($fileName)
    {
        $responsiveImages = $this->media->responsive_images ?? [];

        $responsiveImages[] = $fileName;

        $this->media->responsive_images = $responsiveImages;
    
        $this->media->save();
    }
}
