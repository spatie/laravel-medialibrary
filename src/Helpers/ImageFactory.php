<?php

namespace Spatie\Medialibrary\Helpers;

use Spatie\Image\Image;

class ImageFactory
{
    public static function load(string $path): Image
    {
        return Image::load($path)
            ->useImageDriver(config('medialibrary.image_driver'));
    }
}
