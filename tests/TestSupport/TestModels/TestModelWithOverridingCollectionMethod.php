<?php

namespace Spatie\MediaLibrary\Tests\TestSupport\TestModels;

use Spatie\MediaLibrary\Attributes\MediaCollection;

#[MediaCollection(name: 'avatar', singleFile: true)]
class TestModelWithOverridingCollectionMethod extends TestModel
{
    public function registerMediaCollections(): void
    {
        // Re-declares `avatar` without singleFile, which must win over the attribute.
        $this->addMediaCollection('avatar');
    }
}
