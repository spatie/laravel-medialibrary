<?php

use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithTimestamps;

it('touches the parent model when media is added', function () {
    Carbon::setTestNow(now()->subDay());
    $testModel = TestModelWithTimestamps::create(['name' => 'test']);
    $originalUpdatedAt = $testModel->updated_at;

    Carbon::setTestNow();
    $testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $testModel->refresh();
    expect($testModel->updated_at->gt($originalUpdatedAt))->toBeTrue();
});

it('touches the parent model when media is deleted', function () {
    $testModel = TestModelWithTimestamps::create(['name' => 'test']);
    $media = $testModel->addMedia($this->getTestJpg())->toMediaCollection();

    Carbon::setTestNow(now()->addDay());
    $media->delete();

    $testModel->refresh();
    expect($testModel->updated_at->gte(now()->subSecond()))->toBeTrue();
});

it('does not touch the parent model when touch_parent_model is disabled', function () {
    config()->set('media-library.touch_parent_model', false);

    Carbon::setTestNow(now()->subDay());
    $testModel = TestModelWithTimestamps::create(['name' => 'test']);
    $originalUpdatedAt = $testModel->updated_at;

    Carbon::setTestNow();
    $testModel->addMedia($this->getTestJpg())->toMediaCollection();

    $testModel->refresh();
    expect($testModel->updated_at->eq($originalUpdatedAt))->toBeTrue();
});
