<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaCollection;

#[MediaCollection(name: 'avatar', singleFile: true)]
class TestModelWithOverridingCollectionMethod extends TestModel
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar');
    }
}
