<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestUuidPathGenerator;

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

    /** @test */
    public function it_doesnt_move_media_on_change()
    {
        config([
            'media-library.path_generator' => TestUuidPathGenerator::class,
            'media-library.moves_media_on_update' => false,
        ]);

        $mediaItem = $this
            ->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $oldMediaPath = $mediaItem->getPath();

        $this->assertFileExists($oldMediaPath);

        $mediaItem->update(['uuid' => Str::uuid()]);

        $this->assertNotEquals($oldMediaPath, $mediaItem->getPath());
        $this->assertFileExists($oldMediaPath);
        $this->assertFileDoesNotExist($mediaItem->getPath());
    }

    /** @test */
    public function it_moves_media_on_change()
    {
        config([
            'media-library.path_generator' => TestUuidPathGenerator::class,
            'media-library.moves_media_on_update' => true,
        ]);

        $mediaItem = $this
            ->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $oldMediaPath = $mediaItem->getPath();

        $this->assertFileExists($oldMediaPath);

        $mediaItem->update(['uuid' => Str::uuid()]);

        $this->assertNotEquals($oldMediaPath, $mediaItem->getPath());
        $this->assertFileDoesNotExist($oldMediaPath);
        $this->assertFileExists($mediaItem->getPath());
    }
}
