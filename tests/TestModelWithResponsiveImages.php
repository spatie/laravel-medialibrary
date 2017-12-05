<?php

namespace Spatie\MediaLibrary\Tests;

use Spatie\MediaLibrary\Media;

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

        $this->addMediaConversion('keep_original_format')
            ->keepOriginalImageFormat()
            ->nonQueued();
    }
}
