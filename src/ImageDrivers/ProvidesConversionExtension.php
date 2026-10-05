<?php

namespace Spatie\MediaLibrary\ImageDrivers;

use Spatie\MediaLibrary\Conversions\Conversion;

interface ProvidesConversionExtension extends MediaImageDriver
{
    /**
     * The file extension a conversion produced by this driver is stored with.
     * It must be derivable without generating the conversion, so the same
     * value is used when generating, storing, and building urls for the file.
     */
    public function conversionExtension(Conversion $conversion, string $originalExtension): string;
}
