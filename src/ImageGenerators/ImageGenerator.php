<?php

namespace Spatie\MediaLibrary\ImageGenerator;

use Spatie\MediaLibrary\Conversion\Conversion;

interface ImageGenerator
{
    /**
     * Return the name of the media type handled by the driver.
     */
    public function getMediaType() : string;

    /**
     * Verify that a file is this driver media type using it's extension.
     *
     * @param string $extension
     *
     * @return bool
     */
    public function fileExtensionIsType(string $extension) : bool;

    /**
     * Verify that a file is this driver media type using it's mime.
     *
     * @param string $mime
     *
     * @return bool
     */
    public function fileMimeIsType(string $mime) : bool;

    /**
     * Return true if the project as all the requirements to use the thumbnail driver.
     */
    public function hasRequirements() : bool;

    /**
     * Receive a file of type X and return a thumbnail in jpg/png format.
     *
     * @param string $file
     * @param \Spatie\MediaLibrary\Conversion\Conversion $conversion
     *
     * @return string
     */
    public function convertToImage(string $file, Conversion $conversion) : string;
}
