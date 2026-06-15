<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

beforeEach(function () {
    config()->set('queue.default', 'sync');
    cache()->flush();
});

it('runs the then callback after conversions, receiving the media', function () {
    $model = TestModelWithConversion::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->then(function (Media $media) {
            cache()->put('then-called-with', $media->getKey());
        })
        ->toMediaCollection();

    expect($media)->toBeInstanceOf(Media::class)
        ->and(cache()->get('then-called-with'))->toBe($media->getKey());
});

it('runs the then callback immediately for media without derivatives', function () {
    $model = TestModel::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->then(function (Media $media) {
            cache()->put('then-called', true);
        })
        ->toMediaCollection();

    expect(cache()->get('then-called'))->toBeTrue();
});
