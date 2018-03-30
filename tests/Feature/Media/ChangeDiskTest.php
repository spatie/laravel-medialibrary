<?php

namespace Spatie\MediaLibrary\Tests\Feature\Models\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class ChangeDiskTest extends TestCase
{
    /** @test */
    public function it_moves_media_between_disks()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile)->toMediaCollection();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $media->file_name = 'test-new-name.jpg';
        $media->save();

        $this->assertFileNotExists($this->getMediaDirectory($media->id.'/test.jpg'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test-new-name.jpg'));
    }

}
