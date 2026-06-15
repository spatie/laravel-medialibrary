<?php

use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidMediaAttribute;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithMediaAttributes;

afterEach(fn () => MediaAttributeResolver::clearCache());

it('reads collection and conversion attributes from a model class', function () {
    $resolver = new MediaAttributeResolver(TestModelWithMediaAttributes::class);

    expect($resolver->collectionAttributes())->toHaveCount(2)
        ->and($resolver->collectionAttributes()[0])->toBeInstanceOf(MediaCollection::class)
        ->and($resolver->conversionAttributes())->toHaveCount(2)
        ->and($resolver->conversionAttributes()[0])->toBeInstanceOf(MediaConversion::class);
});

it('returns empty arrays for a model without attributes', function () {
    $resolver = new MediaAttributeResolver(TestModel::class);

    expect($resolver->collectionAttributes())->toBe([])
        ->and($resolver->conversionAttributes())->toBe([]);
});

it('throws on a duplicate collection name', function () {
    $resolver = new MediaAttributeResolver(TestModelWithDuplicateCollectionAttribute::class);

    $resolver->collectionAttributes();
})->throws(InvalidMediaAttribute::class);

it('caches parsed attributes per class', function () {
    $first = new MediaAttributeResolver(TestModelWithMediaAttributes::class);
    $second = new MediaAttributeResolver(TestModelWithMediaAttributes::class);

    expect($first->conversionAttributes())->toBe($second->conversionAttributes());
});

it('builds media collection builder objects from attributes', function () {
    $resolver = new MediaAttributeResolver(TestModelWithMediaAttributes::class);

    $collections = $resolver->toMediaCollections();

    expect($collections)->toHaveKey('avatar')
        ->and($collections)->toHaveKey('downloads')
        ->and($collections['avatar'])->toBeInstanceOf(\Spatie\MediaLibrary\MediaCollections\MediaCollection::class)
        ->and($collections['avatar']->name)->toBe('avatar')
        ->and($collections['avatar']->singleFile)->toBeTrue()
        ->and($collections['avatar']->fallbackUrls['default'])->toBe('/default.png');
});

#[\Spatie\MediaLibrary\Attributes\MediaCollection(name: 'avatar')]
#[\Spatie\MediaLibrary\Attributes\MediaCollection(name: 'avatar')]
class TestModelWithDuplicateCollectionAttribute extends TestModel
{
}
