<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can save conversions on a separate disk', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    $this->assertEquals('public', $media->disk);
    $this->assertEquals('secondMediaDisk', $media->conversions_disk);

    $this->assertEquals("/media/{$media->id}/test.jpg", $media->getUrl());
    $this->assertEquals("/media2/{$media->id}/conversions/test-thumb.jpg", $media->getUrl('thumb'));

    $originalFilePath = $media->getPath();

    $this->assertEquals(
        $this->getTestsPath('TestSupport/temp/media/1/test.jpg'),
        $originalFilePath
    );
    $this->assertFileExists($originalFilePath);

    $conversionsFilePath = $media->getPath('thumb');
    $this->assertEquals(
        $this->getTestsPath('TestSupport/temp/media2/1/conversions/test-thumb.jpg'),
        $conversionsFilePath
    );
    $this->assertFileExists($conversionsFilePath);
});

test('the responsive images will get saved on the same disk as the conversions', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    $this->assertFileExists($this->getTempDirectory('media2/1/responsive-images/test___thumb_50_41.jpg'));
});

test('deleting media will also delete conversions on the separate disk', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    $this->assertFileExists($media->getPath('thumb'));

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
    $this->assertFileExists($conversionsFilePath);
});
