<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithConversion extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(50)
            ->height(50)
            ->nonQueued();

        $this
            ->addMediaConversion('keep_original_format')
            ->keepOriginalImageFormat()
            ->nonQueued();
    }
}
