<?php

use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('can set and get a focal point', function () {
    $media = TestModel::first()
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $media->setFocalPoint(70, 30)->save();

    expect($media->getFocalPoint())->toBe(['x' => 70, 'y' => 30]);
    expect($media->hasFocalPoint())->toBeTrue();
});

it('returns null when no focal point is set', function () {
    $media = TestModel::first()
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    expect($media->getFocalPoint())->toBeNull();
    expect($media->hasFocalPoint())->toBeFalse();
});

it('can set a focal point with float values', function () {
    $media = TestModel::first()
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $media->setFocalPoint(33.5, 66.7)->save();

    expect($media->getFocalPoint())->toBe(['x' => 33.5, 'y' => 66.7]);
    expect($media->hasFocalPoint())->toBeTrue();
});

it('persists the focal point after refresh', function () {
    $media = TestModel::first()
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();

    $media->setFocalPoint(50, 50)->save();

    $media->refresh();

    expect($media->getFocalPoint())->toBe(['x' => 50, 'y' => 50]);
    expect($media->hasFocalPoint())->toBeTrue();
});
