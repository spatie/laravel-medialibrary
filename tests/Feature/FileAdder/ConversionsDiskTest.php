<?php

it('can save conversions on a separate disk', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    expect($media->disk)->toEqual('public');
    expect($media->conversions_disk)->toEqual('secondMediaDisk');

    expect($media->getUrl())->toEqual("/media/{$media->id}/test.jpg");
    expect($media->getUrl('thumb'))->toEqual("/media2/{$media->id}/conversions/test-thumb.jpg");

    $originalFilePath = $media->getPath();

    $this->assertEquals(
        $this->getTestsPath('TestSupport/temp/media/1/test.jpg'),
        $originalFilePath
    );
    expect($originalFilePath)->toBeFile();

    $conversionsFilePath = $media->getPath('thumb');
    $this->assertEquals(
        $this->getTestsPath('TestSupport/temp/media2/1/conversions/test-thumb.jpg'),
        $conversionsFilePath
    );
    expect($conversionsFilePath)->toBeFile();
});

test('the responsive images will get saved on the same disk as the conversions', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    expect($this->getTempDirectory('media2/1/responsive-images/test___thumb_50_41.jpg'))->toBeFile();
});

test('deleting media will also delete conversions on the separate disk', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    expect($media->getPath('thumb'))->toBeFile();

    $media->delete();

    $this->assertFileDoesNotExist($media->getPath('thumb'));

    $originalFilePath = $media->getPath();
    $this->assertFileDoesNotExist($originalFilePath);
});

it('will store the conversion on the disk specified in on the media collection', function () {
    $media = $this->testModelWithConversionsOnOtherDisk
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('thumb');

    $conversionsFilePath = $media->getPath('thumb');
    $this->assertEquals(
        $this->getTestsPath('TestSupport/temp/media2/1/conversions/test-thumb.jpg'),
        $conversionsFilePath
    );
    expect($conversionsFilePath)->toBeFile();
});

it('uses the globally configured conversions disk when no other disk is specified', function () {
    config()->set('media-library.conversions_disk_name', 'secondMediaDisk');

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    expect($media->disk)->toEqual('public');
    expect($media->conversions_disk)->toEqual('secondMediaDisk');

    expect($media->getPath('thumb'))->toBeFile();
    $this->assertEquals(
        $this->getTestsPath('TestSupport/temp/media2/1/conversions/test-thumb.jpg'),
        $media->getPath('thumb')
    );
});

it('falls back to the originals disk when the global conversions disk is empty', function () {
    config()->set('media-library.conversions_disk_name', null);

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    expect($media->conversions_disk)->toEqual($media->disk);
});

it('lets a per-call storingConversionsOnDisk override the global config', function () {
    config()->set('media-library.conversions_disk_name', 'public');

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    expect($media->conversions_disk)->toEqual('secondMediaDisk');
});

it('lets a per-collection conversions disk override the global config', function () {
    config()->set('media-library.conversions_disk_name', 'public');

    $media = $this->testModelWithConversionsOnOtherDisk
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('thumb');

    // The collection registers 'secondMediaDisk' via storeConversionsOnDisk(),
    // which must win over the global config value.
    expect($media->conversions_disk)->toEqual('secondMediaDisk');
});
