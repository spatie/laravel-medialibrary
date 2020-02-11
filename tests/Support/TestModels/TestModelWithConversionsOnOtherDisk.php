<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

use Spatie\MediaLibrary\Models\Media;

class TestModelWithConversionsOnOtherDisk extends TestModel
{

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
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
