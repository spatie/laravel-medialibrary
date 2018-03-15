<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

use Spatie\MediaLibrary\Models\Media;

class TestModelWithResponsiveImages extends TestModel
{
    /**
     * Register the conversions that should be performed.
     *
     * @return array
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->withResponsiveImages()
            ->width(50)
            ->nonQueued();

        $this->addMediaConversion('otherImageConversion')
            ->greyscale();
    }
}
