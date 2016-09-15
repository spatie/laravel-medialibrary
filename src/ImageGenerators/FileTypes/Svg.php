<?php

namespace Spatie\MediaLibrary\ImageGenerator\FileTypes;

use ImagickPixel;
use Spatie\MediaLibrary\ImageGenerator\ImageGenerator;
use Spatie\MediaLibrary\Conversion\Conversion;

class Svg implements ImageGenerator
{
    /**
     * Return the name of the media type handled by the driver.
     */
    public function getMediaType() : string
    {
        return 'svg';
    }

    /**
     * Verify that a file is this driver media type using it's extension.
     */
    public function fileExtensionIsType(string $extension) : bool
    {
        return $extension === 'svg';
    }

    /**
     * Verify that a file is this driver media type using it's mime.
     */
    public function fileMimeIsType(string $mime) : bool
    {
        return $mime === 'image/svg+xml';
    }

    public function hasRequirements() : bool
    {
        return class_exists('Imagick');
    }

    /**
     * Receive a file of type svg and return a thumbnail in png.
     */
    public function convertToImage(string $file, Conversion $conversion) : string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.png';

        $image = new \Imagick();
        $image->readImage($file);
        $image->setBackgroundColor(new ImagickPixel('none'));
        $image->setImageFormat('png32');

        file_put_contents($imageFile, $image);

        return $imageFile;
    }
}
