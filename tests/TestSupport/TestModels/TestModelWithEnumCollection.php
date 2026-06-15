<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Tests\TestSupport\MediaCollectionEnum;

#[MediaCollection(name: MediaCollectionEnum::Avatar, singleFile: true)]
class TestModelWithEnumCollection extends TestModel
{
    public function registerMediaCollections(): void {}
}
