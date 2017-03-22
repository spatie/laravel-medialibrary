<?php

namespace Spatie\MediaLibrary\Test\Media;

use File;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModel;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_will_remove_the_files_when_deleting_a_media_object()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $this->testModel->delete();

        $this->assertFalse(File::isDirectory($this->getMediaDirectory($media->id)));
    }

    /**
     * @test
     */
    public function it_will_not_remove_the_files_when_shouldDeletePreservingMedia_returns_true()
    {
        $testModelClass = new class() extends TestModel {
            public function shouldDeletePreservingMedia()
            {
                return true;
            }
        };

        $testModel = $testModelClass::find($this->testModel->id);

        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $testModel = $testModel->fresh();

        $testModel->delete();

        $this->assertNotNull(Media::find($media->id));
    }

    /**
     * @test
     */
    public function it_will_remove_the_files_when_shouldDeletePreservingMedia_returns_true()
    {
        $testModelClass = new class() extends TestModel {
            public function shouldDeletePreservingMedia()
            {
                return false;
            }
        };

        $testModel = $testModelClass::find($this->testModel->id);

        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $testModel = $testModel->fresh();

        $testModel->delete();

        $this->assertNull(Media::find($media->id));
    }
}
