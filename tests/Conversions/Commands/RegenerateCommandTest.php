<?php

use Illuminate\Support\Facades\Queue;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

it('can regenerate all files', function () {
    $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $createdAt = filemtime($derivedImage);

    unlink($derivedImage);

    $this->assertFileDoesNotExist($derivedImage);

    sleep(1);

    $this->artisan('media-library:regenerate');

    expect($derivedImage)->toBeFile();
    expect(filemtime($derivedImage))->toBeGreaterThan($createdAt);
});

it('can regenerate only missing files', function () {
    $mediaExists = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $mediaMissing = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.png'))
        ->toMediaCollection('images');

    $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/test-thumb.jpg");

    $derivedMissingImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-thumb.jpg");

    $existsCreatedAt = filemtime($derivedImageExists);

    $missingCreatedAt = filemtime($derivedMissingImage);

    unlink($derivedMissingImage);

    $this->assertFileDoesNotExist($derivedMissingImage);

    sleep(1);

    $this->artisan('media-library:regenerate', [
        '--only-missing' => true,
    ]);

    expect($derivedMissingImage)->toBeFile();

    expect(filemtime($derivedImageExists))->toBe($existsCreatedAt);

    expect(filemtime($derivedMissingImage))->toBeGreaterThan($missingCreatedAt);
});

it('can regenerate missing files queued', function () {
    $mediaExists = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $mediaMissing = $this
        ->testModelWithConversionQueued
        ->addMedia($this->getTestFilesDirectory('test.png'))
        ->toMediaCollection('images');

    $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/test-thumb.jpg");

    $derivedMissingImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-thumb.jpg");

    $existsCreatedAt = filemtime($derivedImageExists);

    $missingCreatedAt = filemtime($derivedMissingImage);

    unlink($derivedMissingImage);

    $this->assertFileDoesNotExist($derivedMissingImage);

    sleep(1);

    $this->artisan('media-library:regenerate', [
        '--only-missing' => true,
    ]);

    expect($derivedMissingImage)->toBeFile();

    expect(filemtime($derivedImageExists))->toBe($existsCreatedAt);

    expect(filemtime($derivedMissingImage))->toBeGreaterThan($missingCreatedAt);
});

it('can regenerate all files of named conversions', function () {
    $media = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedMissingImage = $this->getMediaDirectory("{$media->id}/conversions/test-keep_original_format.jpg");

    unlink($derivedImage);
    unlink($derivedMissingImage);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedMissingImage);

    $this->artisan('media-library:regenerate', [
        '--only' => 'thumb',
    ]);

    expect($derivedImage)->toBeFile();
    $this->assertFileDoesNotExist($derivedMissingImage);
});

it('can regenerate only missing files of named conversions', function () {
    $mediaExists = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $mediaMissing = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.png'))
        ->toMediaCollection('images');

    $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/test-thumb.jpg");
    $derivedMissingImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-thumb.jpg");
    $derivedMissingImageOriginal = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-keep_original_format.png");

    $existsCreatedAt = filemtime($derivedImageExists);
    $missingCreatedAt = filemtime($derivedMissingImage);

    unlink($derivedMissingImage);
    unlink($derivedMissingImageOriginal);

    $this->assertFileDoesNotExist($derivedMissingImage);
    $this->assertFileDoesNotExist($derivedMissingImageOriginal);

    sleep(1);

    $this->artisan('media-library:regenerate', [
        '--only-missing' => true,
        '--only' => 'thumb',
    ]);

    expect($derivedMissingImage)->toBeFile();
    $this->assertFileDoesNotExist($derivedMissingImageOriginal);
    expect(filemtime($derivedImageExists))->toBe($existsCreatedAt);
    expect(filemtime($derivedMissingImage))->toBeGreaterThan($missingCreatedAt);
});

it('can regenerate files by media ids', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media2 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

    unlink($derivedImage);
    unlink($derivedImage2);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedImage2);

    $this->artisan('media-library:regenerate', ['--ids' => [2]]);

    $this->assertFileDoesNotExist($derivedImage);
    expect($derivedImage2)->toBeFile();
});

it('can regenerate files by comma separated media ids', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media2 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

    unlink($derivedImage);
    unlink($derivedImage2);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedImage2);

    $this->artisan('media-library:regenerate', ['--ids' => ['1,2']]);

    expect($derivedImage)->toBeFile();
    expect($derivedImage2)->toBeFile();
});

it('can regenerate files even if there are files missing', function () {
    $media = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    unlink($this->getMediaDirectory($media->id.'/test.jpg'));

    $this->artisan('media-library:regenerate')->assertExitCode(0);
});

it('can regenerate responsive images', function () {
    $media = $this
        ->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->withResponsiveImages()
        ->toMediaCollection();

    $responsiveImages = glob($this->getMediaDirectory($media->id.'/responsive-images/*'));

    array_map('unlink', $responsiveImages);

    $this->artisan('media-library:regenerate', ['--with-responsive-images' => true])->assertExitCode(0);

    foreach ($responsiveImages as $image) {
        expect($image)->toBeFile();
    }
});

it('can regenerate files by starting from id', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media2 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

    unlink($derivedImage);
    unlink($derivedImage2);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedImage2);

    $this->artisan('media-library:regenerate', ['--starting-from-id' => $media2->getKey()]);

    $this->assertFileDoesNotExist($derivedImage);
    expect($derivedImage2)->toBeFile();
});

it('can regenerate files starting after the provided id', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media2 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

    unlink($derivedImage);
    unlink($derivedImage2);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedImage2);

    $this->artisan('media-library:regenerate', [
        '--starting-from-id' => $media->getKey(),
        '--exclude-starting-id' => true,
    ]);

    $this->assertFileDoesNotExist($derivedImage);
    expect($derivedImage2)->toBeFile();
});

it('can regenerate files starting after the provided id with shortcut', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media2 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

    unlink($derivedImage);
    unlink($derivedImage2);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedImage2);

    $this->artisan('media-library:regenerate', [
        '--starting-from-id' => $media->getKey(),
        '-X' => true,
    ]);

    $this->assertFileDoesNotExist($derivedImage);
    expect($derivedImage2)->toBeFile();
});

it('can regenerate files starting from id with model type', function () {
    $media = $this->testModelWithConversionsOnOtherDisk
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media2 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $media3 = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('images');

    $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
    $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");
    $derivedImage3 = $this->getMediaDirectory("{$media3->id}/conversions/test-thumb.jpg");

    unlink($derivedImage);
    unlink($derivedImage2);
    unlink($derivedImage3);

    $this->assertFileDoesNotExist($derivedImage);
    $this->assertFileDoesNotExist($derivedImage2);
    $this->assertFileDoesNotExist($derivedImage3);

    $this->artisan('media-library:regenerate', [
        '--starting-from-id' => $media->getKey(),
        'modelType' => TestModelWithConversion::class,
    ]);

    $this->assertFileDoesNotExist($derivedImage);
    expect($derivedImage2)->toBeFile();
    expect($derivedImage3)->toBeFile();
});

it('can set updated_at column when regenerating', function () {
    $this->travelTo('2020-01-01 00:00:00');
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    $this->travelBack();

    $this->artisan('media-library:regenerate');

    $media->refresh();

    expect($media->updated_at)->toBeGreaterThanOrEqual(now()->subSeconds(5));
});

it('can force queue non-queued conversions', function () {
    Queue::fake();

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->toMediaCollection('images');

    unlink($thumbConversion = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg"));

    $this->artisan('media-library:regenerate', ['--queue-all' => true]);

    $this->assertFileDoesNotExist($this->getMediaDirectory($thumbConversion));

    Queue::assertPushed(PerformConversionsJob::class);
});
