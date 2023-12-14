<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;

class Avif extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): string
    {
        $pathToImageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.png';

        $image = imagecreatefromavif($file);

        imagepng($image, $pathToImageFile, 9);

        imagedestroy($image);

        return $pathToImageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        if (! function_exists('imagecreatefromavif')) {
            return false;
        }

        if (! function_exists('imagepng')) {
            return false;
        }

        if (! function_exists('imagedestroy')) {
            return false;
        }

        return true;
    }

    public function supportedExtensions(): Collection
    {
        return collect(['avif']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['image/avif']);
    }
}
