<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithConversionReplacingOriginal extends TestModel
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('replace_original')
            ->format('jpg')
            ->width(200)
            ->height(200)
            ->replaceOriginal();
    }
}
