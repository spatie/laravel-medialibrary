<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithCloudflareModeAResponsive extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        // Responsive images on the file storing cloudflare driver is not allowed.
        $this->addMediaConversion('avatar')
            ->useImageDriver('cloudflare')
            ->withResponsiveImages()
            ->nonQueued()
            ->manipulate(fn (CloudflareImage $image) => $image->width(300)->format(CloudflareFormat::Webp));
    }
}
