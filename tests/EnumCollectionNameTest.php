<?php

use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\MediaCollectionEnum;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithEnumCollection;

beforeEach(fn () => MediaAttributeResolver::clearCache());

it('accepts a backed enum as the collection name in the MediaCollection attribute', function () {
    $attribute = new MediaCollection(name: MediaCollectionEnum::Avatar);

    expect($attribute->name)->toBe('avatar');
});

it('accepts backed enums in the MediaConversion collections', function () {
    $attribute = new MediaConversion(name: 'thumb', collections: [MediaCollectionEnum::Avatar, 'images']);

    expect($attribute->collections)->toBe(['avatar', 'images']);
});

it('adds and retrieves media using an enum collection name', function () {
    $model = TestModelWithEnumCollection::create(['name' => 'test']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection(MediaCollectionEnum::Avatar);

    expect($model->getMedia(MediaCollectionEnum::Avatar))->toHaveCount(1)
        ->and($model->getFirstMedia(MediaCollectionEnum::Avatar)->collection_name)->toBe('avatar');
});

it('prunes a singleFile enum collection defined via attribute', function () {
    $model = TestModelWithEnumCollection::create(['name' => 'test']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection(MediaCollectionEnum::Avatar);
    $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection(MediaCollectionEnum::Avatar);

    expect($model->getMedia(MediaCollectionEnum::Avatar))->toHaveCount(1);
});
