<?php

use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\Conversions\Jobs\RunMediaCallbacksJob;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

it('runs the then closure with the media', function () {
    $model = TestModel::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    cache()->forget('then-media-id');

    $job = new RunMediaCallbacksJob(
        new SerializableClosure(function ($media) {
            cache()->put('then-media-id', $media->getKey());
        }),
        $media,
    );

    $job->handle();

    expect(cache()->get('then-media-id'))->toBe($media->getKey());
});

it('does nothing when the then closure is null', function () {
    $model = TestModel::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $job = new RunMediaCallbacksJob(null, $media);

    $job->handle();
})->throwsNoExceptions();
