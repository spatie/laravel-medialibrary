<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

beforeEach(function () {
    config()->set('queue.default', 'sync');
    cache()->flush();
});

it('runs the then callback once when the media is saved again', function () {
    $model = TestModelWithConversion::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->then(fn (Media $media) => cache()->increment('then-count'))
        ->toMediaCollection();

    $media->manipulations = ['thumb' => ['greyscale' => []]];
    $media->save();

    expect(cache()->get('then-count'))->toBe(1);
});

it('attaches the callbacks of the adder the media was added with', function () {
    $model = new TestModel(['name' => 'test']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('first');

    $model
        ->addMedia($this->getTestPng())
        ->preservingOriginal()
        ->then(fn (Media $media) => cache()->put('then-collection', $media->collection_name))
        ->toMediaCollection('second');

    $model->save();

    expect(cache()->get('then-collection'))->toBe('second');
});

it('applies the responsive images setting of the adder the media was added with', function () {
    $model = new TestModel(['name' => 'test']);

    $model
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withResponsiveImages()
        ->then(fn (Media $media) => cache()->put('responsive-count', count($media->responsive_images)))
        ->toMediaCollection('first');

    $model
        ->addMedia($this->getTestPng())
        ->preservingOriginal()
        ->then(fn (Media $media) => cache()->put('plain-count', count($media->responsive_images)))
        ->toMediaCollection('second');

    $model->save();

    expect(cache()->get('responsive-count'))->toBeGreaterThan(0)
        ->and(cache()->get('plain-count'))->toBe(0);
});

it('does not generate responsive images for media the local generator cannot read', function () {
    $model = TestModel::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestPdf())
        ->preservingOriginal()
        ->withResponsiveImages()
        ->then(fn (Media $media) => cache()->put('then-called', true))
        ->catch(fn (Throwable $exception) => cache()->put('catch-message', $exception->getMessage()))
        ->toMediaCollection();

    expect(cache()->get('catch-message'))->toBeNull()
        ->and(cache()->get('then-called'))->toBeTrue()
        ->and($media->fresh()->responsive_images)->toBe([]);
});
