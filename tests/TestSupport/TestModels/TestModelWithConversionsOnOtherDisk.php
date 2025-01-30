<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithConversionsOnOtherDisk extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->withResponsiveImages()
            ->width(50)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('thumb')
            ->storeConversionsOnDisk('secondMediaDisk');
    }
}
