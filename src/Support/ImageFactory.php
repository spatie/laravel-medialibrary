<?php

namespace Spatie\Medialibrary\Support;

use Spatie\Image\Image;

class ImageFactory
{
    public static function load(string $path): Image
    {
        return Image::load($path)
            ->useImageDriver(config('medialibrary.image_driver'));
    }
}
