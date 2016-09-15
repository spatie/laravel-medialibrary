<?php

namespace Spatie\MediaLibrary\ImageGenerator;

use Spatie\MediaLibrary\Conversion\Conversion;

interface ImageGenerator
{
    public function canConvert(string $path);

    /**
     * Receive a file and return a thumbnail in jpg/png format.
     *
     * @param string $path
     * @param \Spatie\MediaLibrary\Conversion\Conversion|null $conversion
     *
     * @return string
     */
    public function convert(string $path, Conversion $conversion = null) : string;
}
