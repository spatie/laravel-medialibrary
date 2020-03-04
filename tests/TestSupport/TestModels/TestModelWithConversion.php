<?php

namespace Spatie\Medialibrary\Tests\TestSupport\TestModels;

use Spatie\Medialibrary\MediaCollections\Models\Media;

class TestModelWithConversion extends TestModel
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(50)
            ->nonQueued();

        $this->addMediaConversion('keep_original_format')
            ->keepOriginalImageFormat()
            ->nonQueued();
    }
}
