<?php

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

uses(TestCase::class);

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

    $this->assertCount(0, $model->getMedia('default'));
    $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));

    $this->assertCount(1, $anotherModel->getMedia('images'));
    $this->assertFileExists($this->getMediaDirectory($movedMedia->id.'/test.jpg'));
    $this->assertEquals($movedMedia->model->id, $anotherModel->id);
    $this->assertEquals($movedMedia->name, 'custom-name');
    $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
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

    $this->assertCount(0, $model->getMedia('default'));
    $this->assertFileDoesNotExist($this->getMediaDirectory($media->id.'/test.jpg'));

    $this->assertCount(1, $anotherModel->getMedia('images'));
    $this->assertFileExists($this->getTempDirectory('media2').'/'.$movedMedia->id.'/test.jpg');
    $this->assertEquals($movedMedia->collection_name, 'images');
    $this->assertEquals($movedMedia->disk, $diskName);
    $this->assertEquals($movedMedia->model->id, $anotherModel->id);
    $this->assertEquals($movedMedia->name, 'custom-name');
    $this->assertEquals($movedMedia->getCustomProperty('custom-property-name'), 'custom-property-value');
});
