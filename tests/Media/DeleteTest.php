<?php

namespace Spatie\MediaLibrary\Test\Media;

use File;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\Test\TestModel;
use Spatie\MediaLibrary\Test\TestPathGenerator;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_will_remove_the_files_when_deleting_an_object_that_has_media()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $this->testModel->delete();

        $this->assertFalse(File::isDirectory($this->getMediaDirectory($media->id)));
    }

    /** @test */
    public function it_will_remove_the_files_when_deleting_a_media_instance()
    {
        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $media->delete();

        $this->assertFalse(File::isDirectory($this->getMediaDirectory($media->id)));
    }

    /** @test */
    public function it_will_remove_files_when_deleting_a_media_object_with_a_custom_path_generator()
    {
        config(['medialibrary.custom_path_generator_class' => TestPathGenerator::class]);

        $pathGenerator = new TestPathGenerator();

        $media = $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');
        $path = $pathGenerator->getPath($media);

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $this->testModel->delete();

        $this->assertFalse(File::isDirectory($this->getTempDirectory($path)));
    }

    /**
     * @test
     */
    public function it_will_not_remove_the_files_when_delete_with_softdeletes_is_used()
    {
        $testModel = $this->testModelWithSoftDeletes->find($this->testModel->id);

        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $testModel = $testModel->fresh();

        $testModel->delete();

        $this->assertNotNull(Media::find($media->id));
    }

    /**
     * @test
     */
    public function it_will_remove_the_files_when_forcedelete_with_softdeletes_is_used()
    {
        $testModel = $this->testModelWithSoftDeletes->find($this->testModel->id);

        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $testModel = $testModel->fresh();

        $testModel->forceDelete();

        $this->assertNull(Media::find($media->id));
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
    public function it_will_remove_the_files_when_shouldDeletePreservingMedia_returns_false()
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
