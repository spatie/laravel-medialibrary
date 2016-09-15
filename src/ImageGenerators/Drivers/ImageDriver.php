<?php

namespace Spatie\MediaLibrary\ImageGenerator\Drivers;

use Spatie\MediaLibrary\ImageGenerator\ImageGenerator;
use Spatie\MediaLibrary\Conversion\Conversion;

class ImageDriver implements ImageGenerator
{
    /**
     * Return the name of the media type handled by the driver.
     */
    public function getMediaType() : string
    {
        return 'image';
    }

    /**
     * Verify that a file is this driver media type using it's extension.
     *
     * @param string $extension
     *
     * @return bool
     */
    public function fileExtensionIsType(string $extension) : bool
    {
        return in_array($extension, ['png', 'jpg', 'jpeg', 'gif']);
    }

    /**
     * Verify that a file is this driver media type using it's mime.
     *
     * @param string $mime
     *
     * @return bool
     */
    public function fileMimeIsType(string $mime) : bool
    {
        return in_array($mime, ['image/jpeg', 'image/gif', 'image/png']);
    }

    public function hasRequirements() : bool
    {
        return true;
    }

    /**
     * Image do not need any before conversion processing.
     * @param string $file
     *
     * @param \Spatie\MediaLibrary\Conversion\Conversion $conversion
     *
     * @return string
     */
    public function convertToImage(string $file, Conversion $conversion) : string
    {
        return $file;
    }
}
