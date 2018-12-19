<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;

class TestModelWithGlobalConversionOnlyWithoutCollection extends TestModel
{
    /**
     * Register the media conversions.
     *
     * @param \Spatie\MediaLibrary\Models\Media|null $media
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
    }
}
