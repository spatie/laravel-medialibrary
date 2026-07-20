<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\ResolvesConversionUrls;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CloudflareDeliveryImageDriver implements ResolvesConversionUrls
{
    use BuildsTransformationUrls;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {}

    public function conversionUrl(Media $media, Conversion $conversion): string
    {
        return $this->transformationUrl($media, $conversion);
    }
}
