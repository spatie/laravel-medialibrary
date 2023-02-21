<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Imagick;
use ImagickPixel;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Svg extends ImageGenerator
{
    public function convert(string $file, Conversion $conversion = null, Media $media = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.jpg';

        $image = new Imagick();
        $image->readImage($file);
        $image->setBackgroundColor(new ImagickPixel('none'));
        $image->setImageFormat('jpg');

        file_put_contents($imageFile, $image);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return class_exists(\Imagick::class);
    }

    public function supportedExtensions(): Collection
    {
        return collect('svg');
    }

    public function supportedMimeTypes(): Collection
    {
        return collect('image/svg+xml');
    }
}
