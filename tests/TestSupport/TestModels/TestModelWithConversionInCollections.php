<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

class TestModelWithConversionInCollections extends TestModel
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');

        $this->addMediaConversion('legacy-thumb')->width(100)->nonQueued();
    }
}
