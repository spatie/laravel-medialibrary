<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

use Spatie\MediaLibrary\Models\Media;

class TestModelWithConversionQueued extends TestModel
{
    /**
     * Register the conversions that should be performed.
     *
     * @param Media|null $media
     * @return array
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->width(50);

        $this->addMediaConversion('keep_original_format')
            ->keepOriginalImageFormat();
    }
}
