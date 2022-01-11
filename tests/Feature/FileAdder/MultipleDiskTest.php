<?php

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can add a file to a named collection on a specific disk', function () {
    $collectionName = 'images';
    $diskName = 'secondMediaDisk';

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection($collectionName, $diskName);

    $this->assertEquals($collectionName, $media->collection_name);
    $this->assertEquals($diskName, $media->disk);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
});

it('will throw an exception when using a non existing disk', function () {
    $this->expectException(FileCannotBeAdded::class);

    $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('images', 'diskdoesnotexist');
});

it('will save the derived images on the same disk as the original file', function () {
    $collectionName = 'images';
    $diskName = 'secondMediaDisk';

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection($collectionName, $diskName);

    $this->assertEquals($collectionName, $media->collection_name);
    $this->assertEquals($diskName, $media->disk);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/conversions/test-thumb.jpg');
});

it('can generate urls to media on an alternative disk', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('', 'secondMediaDisk');

    $this->assertEquals("/media2/{$media->id}/test.jpg", $media->getUrl());
    $this->assertEquals("/media2/{$media->id}/conversions/test-thumb.jpg", $media->getUrl('thumb'));
});

it('can put files on the cloud disk configured the filesystems config file', function () {
    $collectionName = 'images';

    $diskName = 'secondMediaDisk';

    app()['config']->set('filesystems.cloud', 'secondMediaDisk');

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollectionOnCloudDisk($collectionName);

    $this->assertEquals($collectionName, $media->collection_name);
    $this->assertEquals($diskName, $media->disk);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
});
