<?php

use Programic\MediaLibrary\MediaCollections\FileAdder;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('can copy media from one model to another', function () {
    /** @var TestModel $model */
    $model = TestModel::create(['name' => 'test']);

    /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $model
        ->addMedia($this->getTestJpg())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->copy($anotherModel, 'images');

    $movedMedia->refresh();

    expect($model->getMedia('default'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    expect($anotherModel->getMedia('images'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/test.jpg'));
    expect($anotherModel->id)->toEqual($movedMedia->model->id);
    expect('custom-name')->toEqual($movedMedia->name);
    expect('custom-property-value')->toEqual($movedMedia->getCustomProperty('custom-property-name'));
});

it('can copy file without extension', function () {
    if (! file_exists(storage_path('media-library/temp'))) {
        mkdir(storage_path('media-library/temp'), 0777, true);
    }

    config(['media-library.temporary_directory_path' => realpath(storage_path('media-library/temp'))]);

    /** @var TestModel $model */
    $model = TestModel::create(['name' => 'test']);

    /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $model
        ->addMedia($this->getTestImageWithoutExtension())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/image'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->copy($anotherModel, 'images');

    $movedMedia->refresh();

    expect($model->getMedia('default'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/image'));

    expect($anotherModel->getMedia('images'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/image'));
    expect($anotherModel->id)->toEqual($movedMedia->model->id);
    expect('custom-name')->toEqual($movedMedia->name);
    expect('custom-property-value')->toEqual($movedMedia->getCustomProperty('custom-property-name'));
});

it('can copy media from one model to another on a specific disk', function () {
    $diskName = 'secondMediaDisk';

    /** @var TestModel $model */
    $model = TestModel::create(['name' => 'test']);

    /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $model
        ->addMedia($this->getTestJpg())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->copy($anotherModel, 'images', $diskName);

    $movedMedia->refresh();

    expect($model->getMedia('default'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    expect($anotherModel->getMedia('images'))->toHaveCount(1);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$movedMedia->id.'/test.jpg');
    expect('images')->toEqual($movedMedia->collection_name);
    expect($diskName)->toEqual($movedMedia->disk);
    expect($anotherModel->id)->toEqual($movedMedia->model->id);
    expect('custom-name')->toEqual($movedMedia->name);
    expect('custom-property-value')->toEqual($movedMedia->getCustomProperty('custom-property-name'));
});

it('can handle file adder callback', function () {
    /** @var TestModel $model */
    $model = TestModel::create(['name' => 'test']);

    /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $model
        ->addMedia($this->getTestJpg())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->copy($anotherModel, 'images', fileAdderCallback: function (FileAdder $fileAdder): FileAdder {
        return $fileAdder->usingFileName('new-filename.jpg');
    });

    $movedMedia->refresh();

    expect($movedMedia->file_name)->toBe('new-filename.jpg');
});

it('can copy file with accent', function () {
    if (! file_exists(storage_path('media-library/temp'))) {
        mkdir(storage_path('media-library/temp'), 0777, true);
    }

    config(['media-library.temporary_directory_path' => realpath(storage_path('media-library/temp'))]);

    /** @var TestModel $model */
    $model = TestModel::create(['name' => 'test']);

    /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $media */
    $media = $model
        ->addMedia($this->getAntaresThumbJpgWithAccent())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/antarèsthumb.jpg'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->copy($anotherModel, 'images');

    $movedMedia->refresh();

    expect($model->getMedia('default'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/antarèsthumb.jpg'));

    expect($anotherModel->getMedia('images'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/antarèsthumb.jpg'));
    expect($anotherModel->id)->toEqual($movedMedia->model->id);
    expect('custom-name')->toEqual($movedMedia->name);
    expect('custom-property-value')->toEqual($movedMedia->getCustomProperty('custom-property-name'));
});

it('preserves original file on copy media item to model', function () {
    $model = TestModel::create(['name' => 'original']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $anotherModel = TestModel::create(['name' => 'target']);

    $anotherMedia = $media->copy($anotherModel);

    expect($media->getPath())->toBeFile();
    expect($anotherMedia->getPath())->toBeFile();
});

it('can copy media with manipulations', function () {
    $model = TestModel::create(['name' => 'original']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->withManipulations(['thumb' => ['greyscale' => [], 'height' => [10]]])
        ->toMediaCollection();

    $anotherModel = TestModel::create(['name' => 'target']);

    $anotherMedia = $media->copy($anotherModel);

    expect($media->manipulations)->toEqual($anotherMedia->manipulations);
});
