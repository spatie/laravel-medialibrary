<?php

use Illuminate\Support\Collection;
use Programic\MediaLibrary\MediaCollections\MediaRepository;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('can handle an empty collection', function () {
    $emptyCollection = $this->testModel->getMedia('images');
    expect($emptyCollection)->toBeInstanceOf(Collection::class);
    expect($emptyCollection)->toHaveCount(0);
});

it('will only get media from the specified collection', function () {
    expect($this->testModel->getMedia('images'))->toHaveCount(0);
    expect($this->testModel->getMedia('downloads'))->toHaveCount(0);
    expect($this->testModel->getMedia())->toHaveCount(0);

    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('images');
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('downloads');
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection();

    $this->testModel = $this->testModel->fresh();

    expect($this->testModel->getMedia('images'))->toHaveCount(1);
    expect($this->testModel->getMedia('downloads'))->toHaveCount(1);
    expect($this->testModel->getMedia())->toHaveCount(1);
});

it('will return media repository', function () {
    expect($this->testModel->getMediaRepository())->toBeInstanceOf(MediaRepository::class);
});

it('returns a media collection as a laravel collection', function () {
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

    expect($this->testModel->getMedia())->toBeInstanceOf(Collection::class);
});

it('returns collections filled with media objects', function () {
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

    expect($this->testModel->getMedia()->first())->toBeInstanceOf(Media::class);
});

it('can get multiple media from the default collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    expect($this->testModel->getMedia())->toHaveCount(2);
});

it('can get multiple media from the default collection empty', function () {
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    expect($this->testModel->getMedia())->toHaveCount(1);
    expect($this->testModel->getMedia(''))->toHaveCount(0);

    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('');

    expect($this->testModel->refresh()->getMedia())->toHaveCount(1);
    expect($this->testModel->refresh()->getMedia(''))->toHaveCount(1);
});

it('can get files from a named collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    expect($this->testModel->getMedia('images'))->toHaveCount(1);
    expect($this->testModel->getMedia('images')[0]->collection_name)->toEqual('images');
});

it('can get files from a collection using a filter', function () {
    $media1 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter1' => 'value1'])
        ->toMediaCollection();

    $media2 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter1' => 'value2'])
        ->toMediaCollection('images');

    $media3 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter2' => 'value1'])
        ->toMediaCollection('images');

    $media4 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter2' => 'value2'])
        ->toMediaCollection('images');

    $collection = $this->testModel->getMedia('images', ['filter2' => 'value1']);
    expect($collection)->toHaveCount(1);
    expect($media3->id)->toBe($collection->first()->id);
});

it('can get files from a collection using a filter callback', function () {
    $media1 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter1' => 'value1'])
        ->toMediaCollection();

    $media2 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter1' => 'value2'])
        ->toMediaCollection('images');

    $media3 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter2' => 'value1'])
        ->toMediaCollection('images');

    $media4 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter2' => 'value2'])
        ->toMediaCollection('images');

    $collection = $this->testModel->getMedia('images', fn (Media $media) => isset($media->custom_properties['filter1']));

    expect($collection)->toHaveCount(1);
    expect($media2->id)->toBe($collection->first()->id);
});

it('can get the first media from a collection', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $media->name = 'first';
    $media->save();

    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $media->name = 'second';
    $media->save();

    expect($this->testModel->getFirstMedia('images')->name)->toEqual('first');
});

it('can get the first media from a collection using a filter', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->withCustomProperties(['extra_property' => 'yes'])
        ->preservingOriginal()
        ->toMediaCollection('images');
    $media->name = 'first';
    $media->save();

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('images');
    $media->name = 'second';
    $media->save();

    expect($this->testModel->getFirstMedia('images', ['extra_property' => 'yes'])->name)->toEqual('first');
});

it('can get the first media from a collection using a filter callback', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->withCustomProperties(['extra_property' => 'yes'])
        ->preservingOriginal()
        ->toMediaCollection('images');
    $media->name = 'first';
    $media->save();

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('images');
    $media->name = 'second';
    $media->save();

    $firstMedia = $this->testModel->getFirstMedia('images', fn (Media $media) => isset($media->custom_properties['extra_property']));

    expect($firstMedia->name)->toEqual('first');
});

it('can get the url to first media in a collection', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $firstMedia->save();

    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $secondMedia->save();

    expect($this->testModel->getFirstMediaUrl('images'))->toEqual($firstMedia->getUrl());
});

it('can get the path to first media in a collection', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $firstMedia->save();

    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $secondMedia->save();

    expect($this->testModel->getFirstMediaPath('images'))->toEqual($firstMedia->getPath());
});

it('can get the default path to the first media in a collection', function ($conversionName, $expectedPath) {
    expect($this->testModel->getFirstMediaPath('avatar', $conversionName))->toEqual($expectedPath);
})->with([
    ['', '/default-path.jpg'],
    ['default', '/default-path.jpg'],
    ['foo', '/default-path.jpg'],
    ['avatar_thumb', '/default-avatar-thumb-path.jpg'],
]);

it('can get the default url to the first media in a collection', function ($conversionName, $expectedUrl) {
    expect($this->testModel->getFirstMediaUrl('avatar', $conversionName))->toEqual($expectedUrl);
})->with([
    ['', '/default-url.jpg'],
    ['default', '/default-url.jpg'],
    ['foo', '/default-url.jpg'],
    ['avatar_thumb', '/default-avatar-thumb-url.jpg'],
]);

it('can get the default path to the first media in a collection if conversion not marked as generated yet', function () {
    $media = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('avatar');

    $avatarThumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-avatar_thumb.jpg");
    unlink($avatarThumbConversion);
    $this->testModelWithConversionQueued->getFirstMedia('avatar')->markAsConversionNotGenerated('avatar_thumb');

    expect($this->testModelWithConversionQueued->getFirstMediaPath('avatar', 'avatar_thumb'))->toEqual($this->getMediaDirectory("{$media->id}/test.jpg"));
});

it('can get the correct path to the converted media in a collection if conversion is marked as generated', function () {
    $media = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('avatar');

    $avatarThumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-avatar_thumb.jpg");
    unlink($avatarThumbConversion);
    $this->testModelWithConversionQueued->getFirstMedia('avatar')->markAsConversionGenerated('avatar_thumb');

    expect($this->testModelWithConversionQueued->getFirstMediaPath('avatar', 'avatar_thumb'))->toEqual($media->getPath('avatar_thumb'));
});

it('can get the default url to the first media in a collection if conversion not marked as generated yet', function () {
    $media = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('avatar');

    $avatarThumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-avatar_thumb.jpg");
    unlink($avatarThumbConversion);
    $this->testModelWithConversionQueued->getFirstMedia('avatar')->markAsConversionNotGenerated('avatar_thumb');

    expect($this->testModelWithConversionQueued->getFirstMediaUrl('avatar', 'avatar_thumb'))->toEqual("/media/{$media->id}/test.jpg");
});

it('will return preloaded media sorting on order column', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $preloadedTestModel = TestModel::with('media')
        ->where('id', $this->testModel->id)
        ->first();

    $this->assertEquals([
        1 => 1,
        2 => 2,
    ], $preloadedTestModel
        ->getMedia('images')
        ->pluck('order_column', 'id')
        ->map(fn ($value) => (int) $value)
        ->toArray());

    $firstMedia->order_column = 3;
    $firstMedia->save();

    $preloadedTestModel = TestModel::with('media')
        ->where('id', $this->testModel->id)
        ->first();

    $this->assertSame([
        2 => 2,
        1 => 3,
    ], $preloadedTestModel
        ->getMedia('images')
        ->pluck('order_column', 'id')
        ->map(fn ($value) => (int) $value)
        ->toArray());
});

it('will cache loaded media', function () {
    DB::enableQueryLog();

    expect($this->testModel->relationLoaded('media'))->toBeFalse();
    expect(DB::getQueryLog())->toHaveCount(0);

    $this->testModel->getMedia('images');

    expect($this->testModel->relationLoaded('media'))->toBeTrue();
    expect(DB::getQueryLog())->toHaveCount(1);

    $this->testModel->getMedia('images');

    expect(DB::getQueryLog())->toHaveCount(1);

    DB::DisableQueryLog();
});

it('returns null when getting first media for an empty collection', function () {
    expect($this->testModel->getFirstMedia())->toBeNull();
});

it('can serialize model', function () {
    expect(unserializeAndSerializeModel($this->testModel))->toEqual($this->testModel);
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    expect(unserializeAndSerializeModel($this->testModel))->toEqual($this->testModel->fresh());
});

it('will get media from the all collections', function () {
    expect($this->testModel->getMedia('images'))->toHaveCount(0);
    expect($this->testModel->getMedia('downloads'))->toHaveCount(0);
    expect($this->testModel->getMedia())->toHaveCount(0);

    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('images');
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('downloads');
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection();

    $this->testModel = $this->testModel->fresh();

    expect($this->testModel->getMedia('*'))->toHaveCount(3);
});
