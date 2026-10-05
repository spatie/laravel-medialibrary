<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[MediaCollection(name: 'images')]
#[MediaConversion(name: 'thumb', width: 150, height: 150)]
class TestModelWithAttributesAndMethods extends TestModel
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('large')->width(2000)->nonQueued();
    }
}
