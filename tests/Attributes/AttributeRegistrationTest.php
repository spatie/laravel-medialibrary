<?php

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidMediaAttribute;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionAttributeAndMethod;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionForUnknownCollection;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversionScopedToDefault;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMediaAttributes;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithOverridingCollectionMethod;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithSameConversionNamePerCollection;

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

it('registers conversions declared via attributes', function () {
    $model = new TestModelWithMediaAttributes;

    $model->registerAllMediaConversions();

    $names = collect($model->mediaConversions)->map(fn (Conversion $conversion) => $conversion->getName())->all();

    expect($names)->toContain('thumb', 'preview');
});

it('lets a method-defined conversion override a same-named attribute conversion', function () {
    $model = new TestModelWithConversionAttributeAndMethod;

    $model->registerAllMediaConversions();

    $thumbs = collect($model->mediaConversions)->filter(fn (Conversion $conversion) => $conversion->getName() === 'thumb');

    expect($thumbs)->toHaveCount(1)
        ->and($thumbs->first()->shouldBeQueued())->toBeFalse();
});

it('throws when an attribute conversion references an unknown collection', function () {
    $model = new TestModelWithConversionForUnknownCollection;

    $model->registerAllMediaConversions();
})->throws(InvalidMediaAttribute::class);

it('keeps same-named conversions that target different collections', function () {
    $model = new TestModelWithSameConversionNamePerCollection;

    $model->registerAllMediaConversions();

    $thumbs = collect($model->mediaConversions)->filter(fn (Conversion $conversion) => $conversion->getName() === 'thumb');

    expect($thumbs)->toHaveCount(2);

    $forA = $thumbs->first(fn (Conversion $conversion) => $conversion->shouldBePerformedOn('collA') && ! $conversion->shouldBePerformedOn('collB'));
    $forB = $thumbs->first(fn (Conversion $conversion) => $conversion->shouldBePerformedOn('collB') && ! $conversion->shouldBePerformedOn('collA'));

    expect($forA)->not->toBeNull()
        ->and($forB)->not->toBeNull();
});

it('allows an attribute conversion scoped to the implicit default collection', function () {
    $model = new TestModelWithConversionScopedToDefault;

    $model->registerAllMediaConversions();

    $names = collect($model->mediaConversions)->map(fn (Conversion $conversion) => $conversion->getName())->all();

    expect($names)->toContain('square');
});
