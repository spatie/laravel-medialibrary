<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[MediaConversion(name: 'thumb', width: 150, queued: true)]
class TestModelWithConversionAttributeAndMethod extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->nonQueued();
    }
}
