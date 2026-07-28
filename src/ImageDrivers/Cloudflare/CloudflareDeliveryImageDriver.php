<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\ResolvesResponsiveConversionUrls;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CloudflareDeliveryImageDriver implements ResolvesResponsiveConversionUrls
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

    public function responsiveConversionUrls(Media $media, Conversion $conversion): array
    {
        // The manipulation closure and the original's url are identical for
        // every width, so resolve them once instead of once per entry.
        $parameters = $this->transformationParameters($conversion);
        $sourceUrl = $media->getFullUrl();

        $urls = [];

        foreach ($this->responsiveWidths() as $width) {
            $widthParameters = [...$parameters, 'width' => $width];

            ksort($widthParameters);

            $urls[$width] = $this->transformationUrlForParameters($widthParameters, $sourceUrl);
        }

        return $urls;
    }

    /**
     * @return array<int, int>
     */
    protected function responsiveWidths(): array
    {
        /** @var array<int, int> $widths */
        $widths = $this->config['responsive_widths'] ?? [320, 640, 960, 1280, 1920];

        sort($widths);

        return $widths;
    }
}
