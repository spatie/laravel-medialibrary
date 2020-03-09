<?php

namespace Spatie\MediaLibrary\Support;

use Spatie\Image\Image;

class ImageFactory
{
    public static function load(string $path): Image
    {
        return Image::load($path)->useImageDriver(config('media-library.image_driver'));
    }
}
