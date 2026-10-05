<?php

namespace Spatie\MediaLibrary\Support;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Image;
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;

class ImageFactory
{
    public static function load(string $path): ImageDriver
    {
        return Image::useImageDriver(app(ImageDriverManager::class)->spatieEngine())
            ->loadFile($path);
    }
}
