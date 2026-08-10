<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithSameConversionNamePerCollection extends TestModel
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('collA')->registerMediaConversions(function (?Media $media = null) {
            $this->addMediaConversion('thumb')->width(100);
        });

        $this->addMediaCollection('collB')->registerMediaConversions(function (?Media $media = null) {
            $this->addMediaConversion('thumb')->width(500);
        });
    }
}
