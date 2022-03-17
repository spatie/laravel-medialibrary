<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithMultipleConversions extends TestModel
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('small')
            ->width(50)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(100)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(200)
            ->nonQueued();
    }
}
