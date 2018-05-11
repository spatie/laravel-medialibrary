<?php

namespace Spatie\MediaLibrary\ImageGenerators\FileTypes;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\BaseGenerator;

class Webp extends BaseGenerator
{
    public function convert(string $file, Conversion $conversion = null) : string
    {
        $pathToImageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.png';

        $image = imagecreatefromwebp($file);

        imagepng($image, $pathToImageFile, 9);

        imagedestroy($image);

        return $pathToImageFile;
    }

    public function requirementsAreInstalled() : bool
    {
        if (! function_exists('imagecreatefromwebp')) {
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

    public function supportedExtensions() : Collection
    {
        return collect(['webp']);
    }

    public function supportedMimeTypes() : Collection
    {
        return collect(['image/webp']);
    }
}
