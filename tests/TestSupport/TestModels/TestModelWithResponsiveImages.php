<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\MaxWidthWidthCalculator;

class TestModelWithResponsiveImages extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->withResponsiveImages()
            ->width(50)
            ->nonQueued();

        $this
            ->addMediaConversion('otherImageConversion')
            ->greyscale();

        $this
            ->addMediaConversion('pngtojpg')
            ->width(700)
            ->quality(1)
            ->background('#ff00ff')
            ->format('jpg')
            ->withResponsiveImages();

        $this
            ->addMediaConversion('lowerQuality')
            ->withResponsiveImages()
            ->quality(60)
            ->nonQueued();

        $this
            ->addMediaConversion('standardQuality')
            ->withResponsiveImages()
            ->nonQueued();


        $this
            ->addMediaConversion('customWidthCalculator')
            ->withResponsiveImages()
            ->withWidthCalculator(new MaxWidthWidthCalculator(200))
            ->nonQueued();
    }
}
