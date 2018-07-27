<?php

namespace Spatie\MediaLibrary\Tests\Feature\Models\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class ChangeDiskTest extends TestCase
{
    /** @test */
    public function it_wil_move_file_to_other_disk_if_it_is_changed_on_the_media_object()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile)->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $media->disk = 'secondMediaDisk';
        $media->save();

        $this->assertFileNotExists($this->getMediaDirectory($media->id.'/test.jpg'));
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/test.jpg');
    }

    /** @test */
    public function it_wil_move_and_rename_file_to_other_disk_if_it_is_changed_on_the_media_object()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile)->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $media->disk = 'secondMediaDisk';
        $media->file_name = 'new.jpg';
        $media->save();

        $this->assertFileNotExists($this->getMediaDirectory($media->id.'/test.jpg'));
        $this->assertFileExists($this->getTempDirectory('media2').'/'.$media->id.'/new.jpg');
    }
}
