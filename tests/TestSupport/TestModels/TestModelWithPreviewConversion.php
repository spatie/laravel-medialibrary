<?php

namespace Programic\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\Image\Enums\Fit;
use Programic\MediaLibrary\MediaCollections\Models\Media;

class TestModelWithPreviewConversion extends TestModel
{
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
}
