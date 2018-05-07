<?php

namespace Spatie\MediaLibrary\Tests\Feature\Models\Media;

use File;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\Tests\Support\TestPathGenerator;
use Spatie\MediaLibrary\Tests\Support\TestModels\TestModel;

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
        config(['medialibrary.path_generator' => TestPathGenerator::class]);

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

    /** @test */
    public function it_will_not_remove_the_file_when_model_uses_softdelete()
    {
        $testModelClass = new class() extends TestModel {
            use SoftDeletes;
        };

        /** @var TestModel $testModel */
        $testModel = $testModelClass::find($this->testModel->id);

        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $testModel = $testModel->fresh();

        $testModel->delete();

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));
    }

    /** @test */
    public function it_will_remove_the_file_when_model_uses_softdelete_with_force()
    {
        $testModelClass = new class() extends TestModel {
            use SoftDeletes;
        };

        /** @var TestModel $testModel */
        $testModel = $testModelClass::find($this->testModel->id);

        $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

        $this->assertTrue(File::isDirectory($this->getMediaDirectory($media->id)));

        $testModel = $testModel->fresh();

        $testModel->forceDelete();

        $this->assertFalse(File::isDirectory($this->getMediaDirectory($media->id)));
    }
}
