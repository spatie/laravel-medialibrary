<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithClosureConversion;
use Spatie\MediaLibrary\Tests\TestSupport\ThrowingConversionsJob;

beforeEach(function () {
    Schema::create('jobs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    config()->set('queue.default', 'database');
    config()->set('media-library.queue_connection_name', 'database');
    config()->set('queue.failed.driver', 'null');
    cache()->flush();
});

it('serializes captured values and manipulation closures and finishes all derivatives before then', function () {
    $model = TestModelWithClosureConversion::create(['name' => 'test']);
    $key = 'queued-result';

    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()
        ->withResponsiveImages()->onQueue('images')
        ->then(static function (Media $media) use ($key) {
            cache()->put($key, [
                $media->getKey(),
                getimagesize($media->getPath('closure-thumb'))[0],
                $media->hasGeneratedConversion('closure-thumb'),
                $media->hasResponsiveImages(),
            ]);
        })->toMediaCollection();

    expect(cache()->get($key))->toBeNull()
        ->and(DB::table('jobs')->where('queue', 'images')->count())->toBe(1)
        ->and($media->fresh()->hasGeneratedConversion('closure-thumb'))->toBeFalse();

    $this->artisan('queue:work', ['connection' => 'database', '--queue' => 'images', '--once' => true]);

    expect(cache()->get($key))->toBe([$media->getKey(), 60, true, true])
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('dispatches only after commit and discards callbacks on rollback', function () {
    DB::beginTransaction();

    $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()
        ->then(static fn (Media $media) => cache()->put('committed', $media->id))
        ->toMediaCollection();

    expect(DB::table('jobs')->count())->toBe(0);

    DB::rollBack();

    expect(DB::table('jobs')->count())->toBe(0)->and(cache()->get('committed'))->toBeNull();

    DB::beginTransaction();

    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()
        ->then(static fn (Media $media) => cache()->put('committed', $media->id))
        ->toMediaCollection();

    expect(DB::table('jobs')->count())->toBe(0);

    DB::commit();
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true]);

    expect(cache()->get('committed'))->toBe($media->id);
});

it('handles a queued conversion failure with a serialized catch-only callback', function () {
    config()->set('media-library.jobs.perform_conversions', ThrowingConversionsJob::class);
    $model = TestModelWithClosureConversion::create(['name' => 'test']);

    $model->addMedia($this->getTestJpg())->preservingOriginal()
        ->catch(static fn (Throwable $exception) => cache()->put('failure', $exception->getMessage()))
        ->toMediaCollection();

    $this->artisan('queue:work', ['connection' => 'database', '--once' => true]);

    expect(cache()->get('failure'))->toBe('Conversion failed on purpose')
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('discards callbacks when their media was deleted before a worker runs', function () {
    Event::fake([JobFailed::class]);
    $model = TestModelWithClosureConversion::create(['name' => 'test']);
    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()
        ->then(static fn (Media $media) => cache()->put('deleted-media', true))
        ->toMediaCollection();

    $media->delete();
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true]);

    expect(cache()->get('deleted-media'))->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(0);

    Event::assertNotDispatched(JobFailed::class);
});

it('allows worker retries when a catch callback rethrows and never runs then on failure', function () {
    config()->set('media-library.jobs.perform_conversions', ThrowingConversionsJob::class);
    Event::listen(JobFailed::class, static fn (JobFailed $event) => cache()->put('failed-job', $event->exception->getMessage()));

    TestModelWithClosureConversion::create(['name' => 'test'])
        ->addMedia($this->getTestJpg())->preservingOriginal()
        ->then(static fn (Media $media) => cache()->put('unexpected-success', true))
        ->catch(static function (Throwable $exception) {
            cache()->increment('attempts');

            throw $exception;
        })->toMediaCollection();

    $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--tries' => 2, '--sleep' => 0]);

    expect(cache()->get('attempts'))->toBe(1)
        ->and(cache()->get('failed-job'))->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(1);

    $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--tries' => 2, '--sleep' => 0]);

    expect(cache()->get('attempts'))->toBe(2)
        ->and(cache()->get('failed-job'))->toBe('Conversion failed on purpose')
        ->and(cache()->get('unexpected-success'))->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(0);
});
