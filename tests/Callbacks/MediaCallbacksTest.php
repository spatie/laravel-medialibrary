<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Tests\TestSupport\ThrowingConversionsJob;
use Throwable;

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

it('runs the catch callback when a derivative job fails', function () {
    config()->set('media-library.jobs.perform_conversions', ThrowingConversionsJob::class);

    $model = TestModelWithConversion::create(['name' => 'test']);

    $model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->then(function (Media $media) {
            cache()->put('then-called', true);
        })
        ->catch(function (Throwable $exception) {
            cache()->put('catch-message', $exception->getMessage());
        })
        ->toMediaCollection();

    expect(cache()->get('catch-message'))->toBe('Conversion failed on purpose')
        ->and(cache()->get('then-called'))->toBeNull();
});

it('runs the then callback after responsive images', function () {
    $model = TestModelWithConversion::create(['name' => 'test']);

    $model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withResponsiveImages()
        ->then(function (Media $media) {
            cache()->put('then-after-responsive', $media->getKey());
        })
        ->toMediaCollection();

    expect(cache()->get('then-after-responsive'))->not->toBeNull();
});
