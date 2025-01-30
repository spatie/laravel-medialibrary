<?php

use Programic\MediaLibrary\MediaLibraryServiceProvider;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestCustomMediaModel;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

beforeEach(function () {
    addMedia($this->testModel);
});

it('can clear a collection', function () {
    expect($this->testModel->getMedia('default'))->toHaveCount(3);
    expect($this->testModel->getMedia('images'))->toHaveCount(3);

    $this->testModel->clearMediaCollection('images');

    expect($this->testModel->getMedia('default'))->toHaveCount(3);
    expect($this->testModel->getMedia('images'))->toHaveCount(0);
});

it('will remove the files when clearing a collection', function () {
    $ids = $this->testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModel->clearMediaCollection('images');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the files when deleting a subject', function () {
    $ids = $this->testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModel->delete();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will remove the files when using a custom model and deleting it', function () {
    config()->set('media-library.media_model', TestCustomMediaModel::class);

    (new MediaLibraryServiceProvider(app()))->register()->boot();

    $testModel = TestModel::create(['name' => 'test']);

    addMedia($testModel);

    expect($testModel->getFirstMedia())->toBeInstanceOf(TestCustomMediaModel::class);

    $ids = $testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $testModel->delete();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeFalse();
    });
});

it('will not remove the files when deleting a subject and preserving media', function () {
    $ids = $this->testModel->getMedia('images')->pluck('id');

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });

    $this->testModel->deletePreservingMedia();

    $ids->map(function ($id) {
        expect(File::isDirectory($this->getMediaDirectory($id)))->toBeTrue();
    });
});

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
