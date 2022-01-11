<?php

use DB;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

uses(TestCase::class);

it('can handle an empty collection', function () {
    $emptyCollection = $this->testModel->getMedia('images');
    $this->assertInstanceOf(Collection::class, $emptyCollection);
    $this->assertCount(0, $emptyCollection);
});

it('will only get media from the specified collection', function () {
    $this->assertCount(0, $this->testModel->getMedia('images'));
    $this->assertCount(0, $this->testModel->getMedia('downloads'));
    $this->assertCount(0, $this->testModel->getMedia());

    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('images');
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection('downloads');
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->preservingOriginal()->toMediaCollection();

    $this->testModel = $this->testModel->fresh();

    $this->assertCount(1, $this->testModel->getMedia('images'));
    $this->assertCount(1, $this->testModel->getMedia('downloads'));
    $this->assertCount(1, $this->testModel->getMedia());
});

it('will return media repository', function () {
    $this->assertInstanceOf(MediaRepository::class, $this->testModel->getMediaRepository());
});

it('returns a media collection as a laravel collection', function () {
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

    $this->assertInstanceOf(Collection::class, $this->testModel->getMedia());
});

it('returns collections filled with media objects', function () {
    $this->testModel->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection();

    $this->assertInstanceOf(Media::class, $this->testModel->getMedia()->first());
});

it('can get multiple media from the default collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $this->assertCount(2, $this->testModel->getMedia());
});

it('can get multiple media from the default collection empty', function () {
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $this->assertCount(1, $this->testModel->getMedia());
    $this->assertCount(0, $this->testModel->getMedia(''));

    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('');

    $this->assertCount(1, $this->testModel->refresh()->getMedia());
    $this->assertCount(1, $this->testModel->refresh()->getMedia(''));
});

it('can get files from a named collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');

    $this->assertCount(1, $this->testModel->getMedia('images'));
    $this->assertEquals('images', $this->testModel->getMedia('images')[0]->collection_name);
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
    $this->assertCount(1, $collection);
    $this->assertSame($collection->first()->id, $media3->id);
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

    $this->assertCount(1, $collection);
    $this->assertSame($collection->first()->id, $media2->id);
});

it('can get the first media from a collection', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $media->name = 'first';
    $media->save();

    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $media->name = 'second';
    $media->save();

    $this->assertEquals('first', $this->testModel->getFirstMedia('images')->name);
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

    $this->assertEquals('first', $this->testModel->getFirstMedia('images', ['extra_property' => 'yes'])->name);
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

    $this->assertEquals('first', $firstMedia->name);
});

it('can get the url to first media in a collection', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $firstMedia->save();

    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $secondMedia->save();

    $this->assertEquals($firstMedia->getUrl(), $this->testModel->getFirstMediaUrl('images'));
});

it('can get the path to first media in a collection', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $firstMedia->save();

    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
    $secondMedia->save();

    $this->assertEquals($firstMedia->getPath(), $this->testModel->getFirstMediaPath('images'));
});

it('can get the default path to the first media in a collection', function () {
    $this->assertEquals('/default.jpg', $this->testModel->getFirstMediaPath('avatar'));
});

it('can get the default url to the first media in a collection', function () {
    $this->assertEquals('/default.jpg', $this->testModel->getFirstMediaUrl('avatar'));
});

it('can get the default path to the first media in a collection if conversion not marked as generated yet', function () {
    $media = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('avatar');

    $avatarThumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-avatar_thumb.jpg");
    unlink($avatarThumbConversion);
    $this->testModelWithConversionQueued->getFirstMedia('avatar')->markAsConversionNotGenerated('avatar_thumb');

    $this->assertEquals($this->getMediaDirectory("{$media->id}/test.jpg"), $this->testModelWithConversionQueued->getFirstMediaPath('avatar', 'avatar_thumb'));
});

it('can get the correct path to the converted media in a collection if conversion is marked as generated', function () {
    $media = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('avatar');

    $avatarThumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-avatar_thumb.jpg");
    unlink($avatarThumbConversion);
    $this->testModelWithConversionQueued->getFirstMedia('avatar')->markAsConversionGenerated('avatar_thumb');

    $this->assertEquals($media->getPath('avatar_thumb'), $this->testModelWithConversionQueued->getFirstMediaPath('avatar', 'avatar_thumb'));
});

it('can get the default url to the first media in a collection if conversion not marked as generated yet', function () {
    $media = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('avatar');

    $avatarThumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-avatar_thumb.jpg");
    unlink($avatarThumbConversion);
    $this->testModelWithConversionQueued->getFirstMedia('avatar')->markAsConversionNotGenerated('avatar_thumb');

    $this->assertEquals("/media/{$media->id}/test.jpg", $this->testModelWithConversionQueued->getFirstMediaUrl('avatar', 'avatar_thumb'));
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
        ->map(fn ($value) => (int)$value)
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
        ->map(fn ($value) => (int)$value)
        ->toArray());
});

it('will cache loaded media', function () {
    DB::enableQueryLog();

    $this->assertFalse($this->testModel->relationLoaded('media'));
    $this->assertCount(0, DB::getQueryLog());

    $this->testModel->getMedia('images');

    $this->assertTrue($this->testModel->relationLoaded('media'));
    $this->assertCount(1, DB::getQueryLog());

    $this->testModel->getMedia('images');

    $this->assertCount(1, DB::getQueryLog());

    DB::DisableQueryLog();
});

// Helpers
function it_returns_false_when_getting_first_media_for_an_empty_collection()
{
    test()->assertFalse(test()->testModel->getFirstMedia());
}
