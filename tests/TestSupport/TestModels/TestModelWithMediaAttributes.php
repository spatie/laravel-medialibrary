<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;

#[MediaCollection(name: 'avatar', singleFile: true, fallbackUrl: '/default.png')]
#[MediaCollection(name: 'downloads')]
#[MediaConversion(name: 'thumb', collections: ['avatar'], width: 150, height: 150, fit: Fit::Crop, format: 'webp')]
#[MediaConversion(name: 'preview', width: 500)]
class TestModelWithMediaAttributes extends TestModel
{
    public function registerMediaCollections(): void {}
}
