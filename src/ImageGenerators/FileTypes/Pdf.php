<?php

namespace Spatie\MediaLibrary\ImageGenerator\FileTypes;

use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\BaseGenerator;
use Spatie\MediaLibrary\Media;

class Pdf extends BaseGenerator
{
    public function convert(Media $media, Conversion $conversion = null) : string
    {
        $file = $media->getPath();

        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        (new \Spatie\PdfToImage\Pdf($file))->saveImage($imageFile);

        return $imageFile;
    }

    public function areRequirementsInstalled() : bool
    {
        if  (! class_exists('Imagick')) {
            return false;
        }

        if (! class_exists('\\Spatie\\PdfToImage\\Pdf')) {
            return false;
        };

        return true;
    }

    public function supportedExtensions() : Collection
    {
        return collect('pdf');
    }

    public function supportedMimeTypes() : Collection
    {
        return collect(['application/pdf']);
    }

    public function supportedTypes() : Collection
    {
        return collect('pdf');
    }
}
