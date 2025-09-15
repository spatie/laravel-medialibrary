<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\MediaLibrary\Tests\Feature\S3Integration\S3TestPathGenerator;
use Spatie\TemporaryDirectory\TemporaryDirectory;

beforeEach(function () {
    if (! canTestS3()) {
        $this->markTestSkipped('Skipping S3 tests because no S3 getenv variables found');
    }

    $this->s3BaseDirectory = getS3BaseTestDirectory();

    config()->set('media-library.path_generator', S3TestPathGenerator::class);
});

afterEach(function () {
    cleanUpS3();

    config()->set('media-library.path_generator', null);
});

it('can add media from a disk to s3', function () {
    $randomNumber = rand();

    $fileName = "test{$randomNumber}.jpg";

    Storage::disk('s3_disk')->put("tmp/{$fileName}", file_get_contents($this->getTestJpg()));

    $media = $this->testModel
        ->addMediaFromDisk("tmp/{$fileName}", 's3_disk')
        ->toMediaCollection('default', 's3_disk');

    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/{$fileName}");
});

it('can store a file on s3', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
});

it('can store a file and its conversion on s3', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg");
});

it('can delete a file on s3', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");

    $media->delete();

    assertS3FileNotExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
});

it('deletes file conversions on s3', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
    assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg");

    $media->delete();

    assertS3FileNotExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
    assertS3FileNotExists("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg");
});

it('retrieve a media url from s3', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('default', 's3_disk');

    $this->assertEquals(
        s3BaseUrl()."/{$this->s3BaseDirectory}/{$media->id}/test.jpg",
        $media->getUrl()
    );

    $this->assertEquals(
        sha1(file_get_contents($this->getTestJpg())),
        sha1(file_get_contents($media->getUrl()))
    );
});

it('retrieve a media conversion url from s3', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    $this->assertEquals(
        s3BaseUrl()."/{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg",
        $media->getUrl('thumb')
    );
});

it('retrieves a temporary media url from s3 when s3 root not empty', function () {
    config()->set('filesystems.disks.s3_disk.root', 'test-root');

    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('default', 's3_disk');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/test.jpg",
        $media->getTemporaryUrl(Carbon::now()->addMinutes(5))
    );

    $this->assertStringNotContainsString(
        '/test-root/test-root',
        $media->getTemporaryUrl(Carbon::now()->addMinutes(5))
    );

    $this->assertEquals(
        sha1(file_get_contents($this->getTestJpg())),
        sha1(file_get_contents($media->getTemporaryUrl(Carbon::now()->addMinutes(5))))
    );
});

it('can get the temporary url to first media in a collection', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
    $firstMedia->save();

    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
    $secondMedia->save();

    expect($this->testModel->getFirstTemporaryUrl(Carbon::now()->addMinutes(5), 'images'))->toEqual($firstMedia->getTemporaryUrl(Carbon::now()->addMinutes(5)));
});

it('can get the temporary url to first media in a collection when no expiration passed', function () {
    $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
    $firstMedia->save();

    $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
    $secondMedia->save();

    expect($this->testModel->getFirstTemporaryUrl(collectionName: 'images'))->toEqual($firstMedia->getTemporaryUrl(Carbon::now()->addMinutes(5)));
});

it('retrieves a temporary url for media when no expiration passed', function () {
    $media = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
    $media->save();

    expect($media->getTemporaryUrl())
        ->toEqual($media->getTemporaryUrl(Carbon::now()->addMinutes(5)));
});

it('retrieves a temporary media conversion url from s3', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg",
        $media->getTemporaryUrl(Carbon::now()->addMinutes(5), 'thumb')
    );
});

it('can regenerate only missing with s3 disk', function () {
    $mediaExists = $this
        ->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    $mediaMissing = $this
        ->testModelWithConversion
        ->addMedia($this->getTestPng())
        ->toMediaCollection('default', 's3_disk');

    $derivedImageExists = "{$this->s3BaseDirectory}/{$mediaExists->id}/conversions/test-thumb.jpg";
    $derivedMissingImage = "{$this->s3BaseDirectory}/{$mediaMissing->id}/conversions/test-thumb.jpg";

    $existsCreatedAt = Storage::disk('s3_disk')->lastModified($derivedImageExists);
    $missingCreatedAt = Storage::disk('s3_disk')->lastModified($derivedMissingImage);

    Storage::disk('s3_disk')->delete($derivedMissingImage);

    assertS3FileNotExists($derivedMissingImage);

    sleep(1);

    $this->artisan('media-library:regenerate', [
        '--only-missing' => true,
    ]);

    assertS3FileExists($derivedMissingImage);

    expect(Storage::disk('s3_disk')->lastModified($derivedImageExists))->toBe($existsCreatedAt);
    expect(Storage::disk('s3_disk')->lastModified($derivedMissingImage))->toBeGreaterThan($missingCreatedAt);
});

it('can regenerate only missing files of named conversions with s3 disk', function () {
    $mediaExists = $this
        ->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('images', 's3_disk');

    $mediaMissing = $this
        ->testModelWithConversion
        ->addMedia($this->getTestPng())
        ->toMediaCollection('images', 's3_disk');

    $derivedImageExists = "{$this->s3BaseDirectory}/{$mediaExists->id}/conversions/test-thumb.jpg";
    $derivedMissingImage = "{$this->s3BaseDirectory}/{$mediaMissing->id}/conversions/test-thumb.jpg";
    $derivedMissingImageOriginal = "{$this->s3BaseDirectory}/{$mediaMissing->id}/conversions/test-keep_original_format.png";

    $existsCreatedAt = Storage::disk('s3_disk')->lastModified($derivedImageExists);
    $missingCreatedAt = Storage::disk('s3_disk')->lastModified($derivedMissingImage);

    Storage::disk('s3_disk')->delete($derivedMissingImage);
    Storage::disk('s3_disk')->delete($derivedMissingImageOriginal);

    assertS3FileNotExists($derivedMissingImage);
    assertS3FileNotExists($derivedMissingImageOriginal);

    sleep(1);

    $this->artisan('media-library:regenerate', [
        '--only-missing' => true,
        '--only' => 'thumb',
    ]);

    assertS3FileExists($derivedMissingImage);
    assertS3FileNotExists($derivedMissingImageOriginal);
    expect(Storage::disk('s3_disk')->lastModified($derivedImageExists))->toBe($existsCreatedAt);
    expect(Storage::disk('s3_disk')->lastModified($derivedMissingImage))->toBeGreaterThan($missingCreatedAt);
});

it('can retrieve a zip with s3 disk', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->toMediaCollection('default', 's3_disk');

    $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia($media);

    ob_start();
    @$zipStreamResponse->toResponse(request())->sendContent();
    $content = ob_get_contents();
    ob_end_clean();

    $temporaryDirectory = (new TemporaryDirectory)->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test.jpg');
});
