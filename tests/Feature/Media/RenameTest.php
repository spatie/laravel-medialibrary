<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class RenameTest extends TestCase
{
    /** @test */
    public function it_wil_rename_the_file_if_it_is_changed_on_the_media_object()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile)->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $media->file_name = 'test-new-name.jpg';
        $media->save();

        $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test-new-name.jpg'));
    }

    /** @test */
    public function it_will_rename_conversions()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModelWithConversion->addMedia($testFile)->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-thumb.jpg'));

        $media->file_name = 'test-new-name.jpg';

        $media->save();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/conversions/test-new-name-thumb.jpg'));
    }

    /** @test */
    public function it_keeps_valid_file_name_when_renaming_with_missing_conversions()
    {
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
    }
}
