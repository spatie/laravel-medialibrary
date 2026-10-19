<?php

use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\MediaCollection as MediaCollectionBuilder;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection as MediaItems;
use Spatie\MediaLibrary\Support\MediaAttributes\MediaAttributeResolver;
use Spatie\MediaLibrary\Tests\TestSupport\MediaCollectionEnum;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
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

it('copies and moves media to enum collections without changing the source on copy', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
    $destination = TestModelWithEnumCollection::create(['name' => 'destination']);

    $copy = $media->copy($destination, MediaCollectionEnum::Avatar);

    expect($copy->collection_name)->toBe('avatar')
        ->and($media->fresh())->not->toBeNull()
        ->and(file_get_contents($copy->getPath()))->toBe(file_get_contents($media->getPath()));

    $moved = $media->move($destination, MediaCollectionEnum::Avatar);

    expect($moved->collection_name)->toBe('avatar')
        ->and($media->fresh())->toBeNull()
        ->and($destination->getMedia(MediaCollectionEnum::Avatar))->toHaveCount(1);
});

it('uses numeric enum values as collection names in attribute conversions', function () {
    $model = NumericCollectionModel::create(['name' => 'numeric']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection(NumericCollection::Photos);

    expect($model->getFirstMedia(NumericCollection::Photos)->id)->toBe($media->id)
        ->and($media->fresh()->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and(getimagesize($media->getPath('thumb'))[0])->toBe(37);

    $model->clearMediaCollection(NumericCollection::Photos);

    expect($model->hasMedia(NumericCollection::Photos))->toBeFalse();
});

it('accepts enums across collection builders, conversion filters and repository queries', function () {
    $model = TestModelWithEnumCollection::create(['name' => 'enums']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection(MediaCollectionEnum::Avatar);
    $repository = app(MediaRepository::class);
    $conversions = new ConversionCollection([Conversion::create('thumb')->performOnCollections(MediaCollectionEnum::Avatar)]);

    expect(MediaCollectionBuilder::create(MediaCollectionEnum::Avatar)->name)->toBe('avatar')
        ->and((new MediaItems)->collectionName(MediaCollectionEnum::Avatar)->collectionName)->toBe('avatar')
        ->and($conversions->getConversions(MediaCollectionEnum::Avatar))->toHaveCount(1)
        ->and($conversions->getConversions('other'))->toHaveCount(0)
        ->and($repository->getCollection($model, MediaCollectionEnum::Avatar)->first()->id)->toBe($media->id)
        ->and($repository->getByCollectionName(MediaCollectionEnum::Avatar)->first()->id)->toBe($media->id)
        ->and($repository->getByModelTypeAndCollectionName($model::class, MediaCollectionEnum::Avatar)->first()->id)->toBe($media->id)
        ->and($repository->getOrphansByCollectionName(MediaCollectionEnum::Avatar)->count())->toBe(0);
});

enum NumericCollection: int
{
    case Photos = 12;
}

#[MediaCollection(name: NumericCollection::Photos)]
#[MediaConversion(name: 'thumb', collections: [NumericCollection::Photos], width: 37, queued: false)]
class NumericCollectionModel extends TestModel {}
