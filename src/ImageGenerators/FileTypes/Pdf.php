<?php

namespace Spatie\MediaLibrary\ImageGenerators\FileTypes;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\BaseGenerator;

class Pdf extends BaseGenerator
{
    public function convert(string $file, Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        (new \Spatie\PdfToImage\Pdf($file))->saveImage($imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        if (! class_exists('Imagick')) {
            return false;
        }

        if (! class_exists('\\Spatie\\PdfToImage\\Pdf')) {
            return false;
        }

        return true;
    }

    public function supportedExtensions(): Collection
    {
        return collect('pdf');
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['application/pdf']);
    }
}
