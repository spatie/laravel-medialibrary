<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Programic\Image\Manipulations;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithPreviewConversion extends TestModel
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }
}
