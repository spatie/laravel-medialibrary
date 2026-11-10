<?php

use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('can move media from one model to another', function () {
    $model = TestModel::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->move($anotherModel, 'images');

    expect($model->getMedia('default'))->toHaveCount(0);
    $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));

    expect($anotherModel->getMedia('images'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/test.jpg'));
    expect($anotherModel->id)->toEqual($movedMedia->model->id);
    expect('custom-name')->toEqual($movedMedia->name);
    expect('custom-property-value')->toEqual($movedMedia->getCustomProperty('custom-property-name'));
});

it('can move media from one model to another on a specific disk', function () {
    $diskName = 'secondMediaDisk';

    $model = TestModel::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->usingName('custom-name')
        ->withCustomProperties(['custom-property-name' => 'custom-property-value'])
        ->toMediaCollection();

    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->move($anotherModel, 'images', $diskName);

    expect($model->getMedia('default'))->toHaveCount(0);
    $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));

    expect($anotherModel->getMedia('images'))->toHaveCount(1);
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$movedMedia->id.'/test.jpg');
    expect('images')->toEqual($movedMedia->collection_name);
    expect($diskName)->toEqual($movedMedia->disk);
    expect($anotherModel->id)->toEqual($movedMedia->model->id);
    expect('custom-name')->toEqual($movedMedia->name);
    expect('custom-property-value')->toEqual($movedMedia->getCustomProperty('custom-property-name'));
});

it('can customize the file adder when moving media', function () {
    $model = TestModel::create(['name' => 'test']);

    $media = $model
        ->addMedia($this->getTestJpg())
        ->usingName('custom-name')
        ->toMediaCollection();

    $anotherModel = TestModel::create(['name' => 'another-test']);

    $movedMedia = $media->move(
        $anotherModel,
        'images',
        '',
        '',
        fn ($fileAdder) => $fileAdder
            ->usingName('adjusted-name')
            ->withCustomProperties(['from-callback' => true])
    );

    expect($model->getMedia('default'))->toHaveCount(0);
    expect($movedMedia->name)->toEqual('adjusted-name');
    expect($movedMedia->getCustomProperty('from-callback'))->toBeTrue();
});

it('keeps the original media when the file adder callback throws while moving', function () {
    $model = TestModel::create(['name' => 'test']);

    $media = $model->addMedia($this->getTestJpg())->toMediaCollection();

    $anotherModel = TestModel::create(['name' => 'another-test']);

    expect(fn () => $media->move(
        $anotherModel,
        'images',
        '',
        '',
        fn () => throw new RuntimeException('nope')
    ))->toThrow(RuntimeException::class);

    expect($model->getMedia('default'))->toHaveCount(1);
    $this->assertFileExists($this->getMediaDirectory($media->id.'/test.jpg'));
});
