<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

use Spatie\MediaLibrary\Models\Media;

class TestModelWithResponsiveImages extends TestModel
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->withResponsiveImages()
            ->width(50)
            ->nonQueued();

        $this->addMediaConversion('otherImageConversion')
            ->greyscale();
    }
}
