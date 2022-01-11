<?php

use File;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestCustomMediaModel;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

uses(TestCase::class);

beforeEach(function () {
    addMedia($this->testModel);
});

it('can clear a collection', function () {
    $this->assertCount(3, $this->testModel->getMedia('default'));
    $this->assertCount(3, $this->testModel->getMedia('images'));

    $this->testModel->clearMediaCollection('images');

    $this->assertCount(3, $this->testModel->getMedia('default'));
    $this->assertCount(0, $this->testModel->getMedia('images'));
});

it('will remove the files when clearing a collection', function () {
    $ids = $this->testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $this->testModel->clearMediaCollection('images');

    $ids->map(function ($id) {
        $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
    });
});

it('will remove the files when deleting a subject', function () {
    $ids = $this->testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $this->testModel->delete();

    $ids->map(function ($id) {
        $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
    });
});

it('will remove the files when using a custom model and deleting it', function () {
    config()->set('media-library.media_model', TestCustomMediaModel::class);

    (new MediaLibraryServiceProvider(app()))->boot();

    $testModel = TestModel::create(['name' => 'test']);

    addMedia($testModel);

    $this->assertInstanceOf(TestCustomMediaModel::class, $testModel->getFirstMedia());

    $ids = $testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $testModel->delete();

    $ids->map(function ($id) {
        $this->assertFalse(File::isDirectory($this->getMediaDirectory($id)));
    });
});

it('will not remove the files when deleting a subject and preserving media', function () {
    $ids = $this->testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });

    $this->testModel->deletePreservingMedia();

    $ids->map(function ($id) {
        $this->assertTrue(File::isDirectory($this->getMediaDirectory($id)));
    });
});

// Helpers
function addMedia(TestModel $model)
{
    foreach (range(1, 3) as $index) {
        $model
            ->addMedia(test()->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $model
            ->addMedia(test()->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('images');
    }
}
