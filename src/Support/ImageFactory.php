<?php

namespace Spatie\MediaLibrary\Support;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Image;

class ImageFactory
{
    public static function load(string $path): ImageDriver
    {
        return Image::useImageDriver(config('media-library.image_driver'))
            ->loadFile($path);
    }
}
