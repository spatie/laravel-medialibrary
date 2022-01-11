<?php

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestUuidPathGenerator;

uses(TestCase::class);

it('can get the sum of all media item sizes', function () {
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
});

it('can get registered media collections', function () {
    // the 'avatar' media collection is registered in
    // \Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel->registerMediaCollections()
    $collections = $this->testModel->getRegisteredMediaCollections();

    $this->assertCount(1, $collections);
    $this->assertInstanceOf(MediaCollection::class, $collections->first());
    $this->assertEquals('avatar', $collections->first()->name);
});

it('doesnt move media on change', function () {
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
});

it('moves media on change', function () {
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
});
