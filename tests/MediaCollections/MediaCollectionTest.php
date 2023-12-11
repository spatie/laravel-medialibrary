<?php

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestUuidPathGenerator;

it('can get the sum of all media item sizes', function () {
    $mediaItem = $this
        ->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();
    expect($mediaItem->size)->toBeGreaterThan(0);

    $anotherMediaItem = $this
        ->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();
    expect($anotherMediaItem->size)->toBeGreaterThan(0);

    $mediaCollection = Media::get();

    $totalSize = $mediaCollection->totalSizeInBytes();

    expect($totalSize)->toEqual($mediaItem->size + $anotherMediaItem->size);
});

it('can get registered media collections', function () {
    // the 'avatar' media collection is registered in
    // \Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel->registerMediaCollections()
    $collections = $this->testModel->getRegisteredMediaCollections();

    expect($collections)->toHaveCount(1);
    expect($collections->first())->toBeInstanceOf(MediaCollection::class);
    expect($collections->first()->name)->toEqual('avatar');
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

    expect($oldMediaPath)->toBeFile();

    $mediaItem->update(['uuid' => Str::uuid()]);

    $this->assertNotEquals($oldMediaPath, $mediaItem->getPath());
    expect($oldMediaPath)->toBeFile();
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

    expect($oldMediaPath)->toBeFile();

    $mediaItem->update(['uuid' => Str::uuid()]);

    $this->assertNotEquals($oldMediaPath, $mediaItem->getPath());
    $this->assertFileDoesNotExist($oldMediaPath);
    expect($mediaItem->getPath())->toBeFile();
});
