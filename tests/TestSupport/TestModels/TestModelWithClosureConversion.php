<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithClosureConversion extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('closure-thumb')
            ->nonQueued()
            ->width(120)
            ->format('webp')
            ->manipulate(fn (ImageDriver $image) => $image->width(60));
    }
}
