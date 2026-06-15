<?php

use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\Conversions\Jobs\RunMediaCallbacksJob;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;
use Throwable;

it('runs the derivative jobs and then the then closure with the media', function () {
    $model = TestModel::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    cache()->forget('order');

    $derivativeJob = new class
    {
        public function handle(): void
        {
            cache()->put('order', ['derivative']);
        }
    };

    $job = new RunMediaCallbacksJob(
        [$derivativeJob],
        new SerializableClosure(function ($media) {
            cache()->put('order', [...cache()->get('order', []), 'then']);
            cache()->put('then-media-id', $media->getKey());
        }),
        null,
        $media,
    );

    $job->handle();

    expect(cache()->get('order'))->toBe(['derivative', 'then'])
        ->and(cache()->get('then-media-id'))->toBe($media->getKey());
});

it('runs the catch closure when a derivative job throws, and skips the then closure', function () {
    $model = TestModel::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    cache()->flush();

    $throwingJob = new class
    {
        public function handle(): void
        {
            throw new RuntimeException('boom');
        }
    };

    $job = new RunMediaCallbacksJob(
        [$throwingJob],
        new SerializableClosure(function () {
            cache()->put('then-called', true);
        }),
        new SerializableClosure(function (Throwable $exception) {
            cache()->put('catch-message', $exception->getMessage());
        }),
        $media,
    );

    $job->handle();

    expect(cache()->get('catch-message'))->toBe('boom')
        ->and(cache()->get('then-called'))->toBeNull();
});

it('rethrows when a derivative job throws and there is no catch closure', function () {
    $model = TestModel::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $throwingJob = new class
    {
        public function handle(): void
        {
            throw new RuntimeException('boom');
        }
    };

    $job = new RunMediaCallbacksJob([$throwingJob], null, null, $media);

    $job->handle();
})->throws(RuntimeException::class, 'boom');

it('does nothing when there are no derivative jobs and no then closure', function () {
    $model = TestModel::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $job = new RunMediaCallbacksJob([], null, null, $media);

    $job->handle();
})->throwsNoExceptions();
