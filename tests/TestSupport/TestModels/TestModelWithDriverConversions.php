<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithDriverConversions extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        // Default (spatie) driver, closure against the real spatie image object.
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->manipulate(fn (ImageDriver $image) => $image->fit(Fit::Crop, 40, 40));

        // Cloudflare renders and we store the file (Mode A).
        $this->addMediaConversion('avatar')
            ->nonQueued()
            ->manipulate(fn (CloudflareImage $image) => $image
                ->width(300)->height(300)->fit(CloudflareFit::Cover)->gravity('face')->format(CloudflareFormat::Webp)
            );

        // Never generated, transformed at the edge on request (Mode B). Responsive
        // images become a srcset of edge urls at different widths, no files stored.
        $this->addMediaConversion('hero')
            ->useImageDriver('cloudflare-delivery')
            ->withResponsiveImages()
            ->manipulate(fn (CloudflareImage $image) => $image
                ->width(1600)->quality(75)->format(CloudflareFormat::Auto)
            );
    }
}
