<?php

namespace Spatie\MediaLibrary\Helpers;

use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaLibrary;

class ImageFactory
{
    public static function load(string $path): Image
    {
        return Image::load($path)
            ->useImageDriver(MediaLibrary::config('image_driver'));
    }
}
