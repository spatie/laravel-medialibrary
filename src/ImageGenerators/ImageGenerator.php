<?php

namespace Spatie\MediaLibrary\ImageGenerator;

use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Media;

interface ImageGenerator
{
    public function canConvert(Media $media);

    /**
     * Receive a file and return a thumbnail in jpg/png format.
     *
     * @param \Spatie\MediaLibrary\Media $media
     * @param \Spatie\MediaLibrary\Conversion\Conversion|null $conversion
     *
     * @return string
     */
    public function convert(Media $media, Conversion $conversion = null) : string;
}
