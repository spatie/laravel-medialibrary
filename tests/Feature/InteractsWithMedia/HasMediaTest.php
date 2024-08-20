<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;

it('returns false for an empty collection', function () {
    expect($this->testModel->hasMedia())->toBeFalse();
});

it('returns true for a non empty collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect($this->testModel->hasMedia())->toBeTrue();
});

it('returns true for a non empty collection in an unsaved model', function () {
    $this->testUnsavedModel->addMedia($this->getTestJpg())->toMediaCollection();

    expect($this->testUnsavedModel->hasMedia())->toBeTrue();
});

it('returns true if any collection is not empty', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect($this->testModel->hasMedia('images'))->toBeTrue();
});

it('returns false for an empty named collection', function () {
    expect($this->testModel->hasMedia('images'))->toBeFalse();
});

it('returns true for a non empty named collection', function () {
    $this->testModel->addMedia($this->getTestJpg())->toMediaCollection('images');

    expect($this->testModel->hasMedia('images'))->toBeTrue();
    expect($this->testModel->hasMedia('downloads'))->toBeFalse();
});

it('returns true for a filtered collection', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withCustomProperties(['test' => true])
        ->toMediaCollection();

    expect($this->testModel->hasMedia('default'))->toBeTrue();
    expect($this->testModel->hasMedia('default', ['test' => true]))->toBeTrue();
    expect($this->testModel->hasMedia('default', ['test' => false]))->toBeFalse();
});

it('returns true using a filter callback', function () {
    $media1 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter1' => 'value1'])
        ->toMediaCollection();

    $media2 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter1' => 'value2'])
        ->toMediaCollection('images');

    $media3 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter2' => 'value1'])
        ->toMediaCollection('images');

    $media4 = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->withCustomProperties(['filter2' => 'value2'])
        ->toMediaCollection('images');

    expect($this->testModel->hasMedia('images', fn (Media $media) => isset($media->custom_properties['filter1'])))->toBeTrue();
    expect($this->testModel->hasMedia('images', fn (Media $media) => isset($media->custom_properties['filter3'])))->toBeFalse();
});
