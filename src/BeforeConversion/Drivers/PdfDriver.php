<?php

namespace Spatie\MediaLibrary\BeforeConversion\Drivers;

use Spatie\MediaLibrary\BeforeConversion\BeforeConversionDriver;
use Spatie\MediaLibrary\Conversion\Conversion;

class PdfDriver implements BeforeConversionDriver
{
    /**
     * Return the name of the media type handled by the driver.
     */
    public function getMediaType() : string
    {
        return 'pdf';
    }

    /**
     * Verify that a file is this driver media type using it's extension.
     */
    public function fileExtensionIsType(string $extension) : bool
    {
        return $extension === 'pdf';
    }

    /**
     * Verify that a file is this driver media type using it's mime.
     */
    public function fileMimeIsType(string $mime) : bool
    {
        return $mime === 'application/pdf';
    }

    public function hasRequirements() : bool
    {
        return class_exists('Imagick') && class_exists('\\Spatie\\PdfToImage\\Pdf');
    }

    /**
     * Receive a file of type pdf and return a thumbnail in jpg.
     */
    public function convertToImage(string $file, Conversion $conversion) : string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        (new \Spatie\PdfToImage\Pdf($file))->saveImage($imageFile);

        return $imageFile;
    }
}
