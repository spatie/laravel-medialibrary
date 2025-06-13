<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Imagick;
use ImagickPixel;
use Spatie\MediaLibrary\Conversions\Conversion;

class Svg extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.png';

        $image = new Imagick;
        $image->setBackgroundColor(new ImagickPixel('none'));
        $image->readImage($file);

        $image->setImageFormat('png32');

        file_put_contents($imageFile, $image);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return class_exists(Imagick::class);
    }

    public function supportedExtensions(): Collection
    {
        return collect(['svg']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['image/svg+xml']);
    }
}
