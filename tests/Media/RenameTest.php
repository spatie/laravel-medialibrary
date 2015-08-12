<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class RenameTest extends TestCase
{
    /**
     * @test
     */
    public function it_wil_rename_the_file_if_it_is_changed_on_the_media_object()
    {
        $testFile = $this->getTestFilesDirectory('test.jpg');

        $media = $this->testModel->addMedia($testFile)->toMediaLibrary();

        $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

        $media->file_name = 'test-new-name.jpg';
        $media->save();

        $this->assertFileNotExists($this->getMediaDirectory($media->id.'/test.jpg'));
        $this->assertFileExists($this->getMediaDirectory($media->id.'/test-new-name.jpg'));
    }
}
