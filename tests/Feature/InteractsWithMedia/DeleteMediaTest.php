<?php

namespace Spatie\MediaLibrary\Tests\Feature\InteractsWithMedia;

use File;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestCustomMediaModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

class DeleteMediaTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->addMedia($this->testModel);
    }

    /** @test */
    public function it_can_clear_a_collection()
    {
        $this->assertCount(3, $this->testModel->getMedia('default'));
        $this->assertCount(3, $this->testModel->getMedia('images'));

        $this->testModel->clearMediaCollection('images');

        $this->assertCount(3, $this->testModel->getMedia('default'));
        $this->assertCount(0, $this->testModel->getMedia('images'));
    }

    /** @test */
    public function it_will_remove_the_files_when_clearing_a_collection()
    {
        $ids = $this->testModel->getMedia('images')->pluck('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $this->testModel->clearMediaCollection('images');

        $ids->map(function ($id) {
            $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
        });
    }

    /** @test */
    public function it_will_remove_the_files_when_deleting_a_subject()
    {
        $ids = $this->testModel->getMedia('images')->pluck('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $this->testModel->delete();

        $ids->map(function ($id) {
            $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
        });
    }

    /** @test */
    public function it_will_remove_the_files_when_using_a_custom_model_and_deleting_it()
    {
        config()->set('media-library.media_model', TestCustomMediaModel::class);

        (new MediaLibraryServiceProvider($this->app))->boot();

        $testModel = TestModel::create(['name' => 'test']);

        $this->addMedia($testModel);

        $this->assertInstanceOf(TestCustomMediaModel::class, $testModel->getFirstMedia());

        $ids = $testModel->getMedia('images')->pluck('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $testModel->delete();

        $ids->map(function ($id) {
            $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
        });
    }

    /** @test */
    public function it_will_not_remove_the_files_when_deleting_a_subject_and_preserving_media()
    {
        $ids = $this->testModel->getMedia('images')->pluck('id');

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });

        $this->testModel->deletePreservingMedia();

        $ids->map(function ($id) {
            $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
        });
    }

    private function addMedia(TestModel $model)
    {
        foreach (range(1, 3) as $index) {
            $model
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaCollection();

            $model
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaCollection('images');
        }
    }
}
