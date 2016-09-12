<?php

namespace Spatie\MediaLibrary\BeforeConversion;

use Spatie\MediaLibrary\Conversion\Conversion;

interface BeforeConversionDriver
{
    /**
     * Return the name of the media type handled by the driver.
     */
    public function getMediaType() : string;

    /**
     * Verify that a file is this driver media type using it's extension.
     */
    public function fileExtensionIsType(string $extension) : bool;

    /**
     * Verify that a file is this driver media type using it's mime.
     */
    public function fileMimeIsType(string $mime) : bool;

    /**
     * Return true if the project as all the requirements to use the thumbnail driver.
     */
    public function hasRequirements() : bool;

    /**
     * Receive a file of type X and return a thumbnail in jpg/png format.
     */
    public function convertToImage(string $file, Conversion $conversion) : string;
}
