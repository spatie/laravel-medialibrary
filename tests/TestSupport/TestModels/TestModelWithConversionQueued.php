<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithConversionQueued extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(50);

        $this
            ->addMediaConversion('avatar_thumb')
            ->performOnCollections('avatar')
            ->width(50);

        $this
            ->addMediaConversion('keep_original_format')
            ->keepOriginalImageFormat();
    }
}
