<?php

use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMediaAttributes;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithOverridingCollectionMethod;

beforeEach(fn () => MediaAttributeResolver::clearCache());

it('registers collections declared via attributes', function () {
    $model = new TestModelWithMediaAttributes;

    $collections = $model->getRegisteredMediaCollections();

    expect($collections->pluck('name')->all())->toContain('avatar', 'downloads');

    $avatar = $model->getMediaCollection('avatar');

    expect($avatar)->toBeInstanceOf(MediaCollection::class)
        ->and($avatar->singleFile)->toBeTrue();
});

it('lets a method-defined collection override a same-named attribute collection', function () {
    $model = new TestModelWithOverridingCollectionMethod;

    expect($model->getMediaCollection('avatar')->singleFile)->toBeFalse();
});
