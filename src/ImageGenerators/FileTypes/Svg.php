<?php

namespace Spatie\MediaLibrary\ImageGenerators\FileTypes;

use Illuminate\Support\Collection;
use ImagickPixel;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\BaseGenerator;

class Svg extends BaseGenerator
{
    public function convert(string $file, Conversion $conversion = null) : string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.png';

        $image = new \Imagick();
        $image->readImage($file);
        $image->setBackgroundColor(new ImagickPixel('none'));
        $image->setImageFormat('png32');

        file_put_contents($imageFile, $image);

        return $imageFile;
    }

    public function requirementsAreInstalled() : bool
    {
        return class_exists('Imagick');
    }

    public function supportedExtensions() : Collection
    {
        return collect('svg');
    }

    public function supportedMimeTypes() : Collection
    {
        return collect('image/svg+xml');
    }

    public function supportedTypes() : Collection
    {
        return collect('svg');
    }
}
