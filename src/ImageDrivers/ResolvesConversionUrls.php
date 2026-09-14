<?php

namespace Spatie\MediaLibrary\ImageDrivers;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface ResolvesConversionUrls extends MediaImageDriver
{
    /**
     * Build the url for a conversion that is never generated as a file.
     */
    public function conversionUrl(Media $media, Conversion $conversion): string;
}
