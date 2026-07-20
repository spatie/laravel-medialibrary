<?php

namespace Spatie\MediaLibrary\ImageDrivers;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface ResolvesResponsiveConversionUrls extends ResolvesConversionUrls
{
    /**
     * Build a set of urls for a virtual conversion at different widths, used to
     * emit a responsive srcset without generating or storing any files.
     *
     * @return array<int, string>  Keyed by width, e.g. [320 => 'https://...', 640 => '...'].
     */
    public function responsiveConversionUrls(Media $media, Conversion $conversion): array;
}
