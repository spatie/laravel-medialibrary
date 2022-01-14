<?php

use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

beforeEach(function () {
    $this->media['model1']['collection1'] = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection1');

    $this->media['model1']['collection2'] = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection2');

    $this->media['model2']['collection1'] = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection1');

    $this->media['model2']['collection2'] = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('collection2');

    expect($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"))->toBeFile();
    expect($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"))->toBeFile();
    expect($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"))->toBeFile();
    expect($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"))->toBeFile();
});

it('can clear all media', function () {
    $this->artisan('media-library:clear');

    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"));
    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
});

it('can clear media from a specific model type', function () {
    $this->artisan('media-library:clear', [
        'modelType' => TestModel::class,
    ]);

    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"));
    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

    expect($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"))->toBeFile();
    expect($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"))->toBeFile();
});

it('can clear media from a specific collection', function () {
    $this->artisan('media-library:clear', [
        'collectionName' => 'collection2',
    ]);

    expect($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"))->toBeFile();
    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

    expect($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"))->toBeFile();
    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"));
});

it('can clear media from a specific model type and collection', function () {
    $this->artisan('media-library:clear', [
        'modelType' => TestModel::class,
        'collectionName' => 'collection2',
    ]);

    expect($this->getMediaDirectory("{$this->media['model1']['collection1']->id}/test.jpg"))->toBeFile();
    $this->assertFileDoesNotExist($this->getMediaDirectory("{$this->media['model1']['collection2']->id}/test.jpg"));

    expect($this->getMediaDirectory("{$this->media['model2']['collection1']->id}/test.jpg"))->toBeFile();
    expect($this->getMediaDirectory("{$this->media['model2']['collection2']->id}/test.jpg"))->toBeFile();
});
