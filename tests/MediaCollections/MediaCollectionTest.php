<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections;

use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;

class MediaCollectionTest extends TestCase
{
    /** @test */
    public function it_can_get_the_sum_of_all_media_item_sizes()
    {
        $mediaItem = $this
            ->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();
        $this->assertGreaterThan(0, $mediaItem->size);

        $anotherMediaItem = $this
            ->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();
        $this->assertGreaterThan(0, $anotherMediaItem->size);

        $mediaCollection = Media::get();

        $totalSize = $mediaCollection->totalSizeInBytes();

        $this->assertEquals($mediaItem->size + $anotherMediaItem->size, $totalSize);
    }

    /** @test */
    public function it_can_get_registered_media_collections()
    {
        // the 'avatar' media collection is registered in
        // \Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel->registerMediaCollections()
        $collections = $this->testModel->getRegisteredMediaCollections();

        $this->assertCount(1, $collections);
        $this->assertInstanceOf(MediaCollection::class, $collections->first());
        $this->assertEquals('avatar', $collections->first()->name);
    }
}
