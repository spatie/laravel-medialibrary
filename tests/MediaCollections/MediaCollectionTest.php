<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections;

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
}
