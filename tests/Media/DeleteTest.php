<?php

namespace Spatie\MediaLibrary\Test\Media;

use File;
use Spatie\MediaLibrary\Test\TestCase;

class DeleteTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_remove_the_files_when_deleting_a_media_object()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $media->delete();

        // failing test
        $this->assertFalse(File::isDirectory($this->getMediaDirectory($media->id)));
    }

    /**
     * @test
     */
    public function it_will_remove_the_files_when_deleting_a_media_object_with_custom_path()
    {
        $media = $this->testModelWithCustomDirectory->addMedia($this->getTestJpg())->toCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory('sub/directory/'.$media->id)));

        $media->delete();

        // failing test
        $this->assertFalse(File::isDirectory($this->getMediaDirectory('sub/directory/'.$media->id)));
    }
}
