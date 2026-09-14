<?php

namespace Spatie\MediaLibrary\ImageDrivers;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface GeneratesConversionFiles extends MediaImageDriver
{
    /**
     * Produce the converted image for the given conversion. The given file is a
     * local temporary copy the driver may manipulate in place. The returned
     * path is the file that will be stored as the conversion.
     */
    public function convert(Media $media, Conversion $conversion, string $file): string;
}
