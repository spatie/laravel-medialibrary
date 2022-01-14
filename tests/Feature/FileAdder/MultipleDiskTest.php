<?php

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

it('can add a file to a named collection on a specific disk', function () {
    $collectionName = 'images';
    $diskName = 'secondMediaDisk';

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection($collectionName, $diskName);

    expect($media->collection_name)->toEqual($collectionName);
    expect($media->disk)->toEqual($diskName);
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

    expect($media->collection_name)->toEqual($collectionName);
    expect($media->disk)->toEqual($diskName);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/conversions/test-thumb.jpg');
});

it('can generate urls to media on an alternative disk', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('', 'secondMediaDisk');

    expect($media->getUrl())->toEqual("/media2/{$media->id}/test.jpg");
    expect($media->getUrl('thumb'))->toEqual("/media2/{$media->id}/conversions/test-thumb.jpg");
});

it('can put files on the cloud disk configured the filesystems config file', function () {
    $collectionName = 'images';

    $diskName = 'secondMediaDisk';

    config()->set('filesystems.cloud', 'secondMediaDisk');

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollectionOnCloudDisk($collectionName);

    expect($media->collection_name)->toEqual($collectionName);
    expect($media->disk)->toEqual($diskName);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
});
