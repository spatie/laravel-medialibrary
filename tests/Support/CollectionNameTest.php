<?php

use Spatie\MediaLibrary\Support\CollectionName;
use Spatie\MediaLibrary\Tests\TestSupport\MediaCollectionEnum;

it('returns a string unchanged', function () {
    expect(CollectionName::resolve('avatar'))->toBe('avatar');
});

it('resolves a backed enum to its value', function () {
    expect(CollectionName::resolve(MediaCollectionEnum::Avatar))->toBe('avatar');
});

it('resolves a list of strings and enums to strings', function () {
    expect(CollectionName::resolveMany(['images', MediaCollectionEnum::Avatar]))
        ->toBe(['images', 'avatar']);
});
