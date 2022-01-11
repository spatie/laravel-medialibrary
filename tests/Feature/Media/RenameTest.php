<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('wil rename the file if it is changed on the media object', function () {
    $testFile = $this->getTestFilesDirectory('test.jpg');

    $media = $this->testModel->addMedia($testFile)->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    $media->file_name = 'test-new-name.jpg';
    $media->save();

    $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));
    $this->assertFileExists($this->getMediaDirectory($media->id.'/test-new-name.jpg'));
});

it('will rename conversions', function () {
    $testFile = $this->getTestFilesDirectory('test.jpg');

    $media = $this->testModelWithConversion->addMedia($testFile)->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg'));

    $media->file_name = 'test-new-name.jpg';

    $media->save();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-new-name-thumb.jpg'));
});

it('keeps valid file name when renaming with missing conversions', function () {
    $testFile = $this->getTestFilesDirectory('test.jpg');

    $media = $this->testModelWithConversion->addMedia($testFile)->toMediaCollection();

    $this->assertFileExists(
        $thumb_conversion = $this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg')
    );

    unlink($thumb_conversion);

    $media->file_name = $new_filename = 'test-new-name.jpg';

    $media->save();

    // Reload attributes from the database
    $media = $media->fresh();

    $this->assertFileExists($media->getPath());
    $this->assertEquals($new_filename, $media->file_name);
});
