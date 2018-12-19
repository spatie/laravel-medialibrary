<?php

namespace Spatie\MediaLibrary\Tests\Support\TestModels;

class TestModelWithCollectionWithoutConversions extends TestModel
{
    /**
     * Register the media collections.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @return void
     */
    public function registerMediaCollections()
    {
        $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
    }
}
