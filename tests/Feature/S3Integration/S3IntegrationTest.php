<?php

use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

uses(TestCase::class);

beforeEach(function () {
    if (! canTestS3()) {
        $this->markTestSkipped('Skipping S3 tests because no S3 env variables found');
    }

    $this->s3BaseDirectory = getS3BaseTestDirectory();

    app()['config']->set('media-library.path_generator', S3TestPathGenerator::class);
});

afterEach(function () {
    cleanUpS3();

    app()['config']->set('media-library.path_generator', null);
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

it('retrieve a media responsive urls from s3', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection('default', 's3_disk');

    $this->assertEquals([
        s3BaseUrl()."/{$this->s3BaseDirectory}/{$media->id}/responsive-images/test___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls('thumb'));
});

it('retrieves a temporary media url from s3', function () {
    $media = $this->testModel
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('default', 's3_disk');

    $this->assertStringContainsString(
        "/{$this->s3BaseDirectory}/{$media->id}/test.jpg",
        $media->getTemporaryUrl(Carbon::now()->addMinutes(5))
    );

    $this->assertEquals(
        sha1(file_get_contents($this->getTestJpg())),
        sha1(file_get_contents($media->getTemporaryUrl(Carbon::now()->addMinutes(5))))
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

    $this->assertEquals($firstMedia->getTemporaryUrl(Carbon::now()->addMinutes(5)), $this->testModel->getFirstTemporaryUrl(Carbon::now()->addMinutes(5), 'images'));
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

test('custom headers are used for all conversions', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->addCustomHeaders([
            'ACL' => 'public-read',
        ])
        ->toMediaCollection('default', 's3_disk');

    $client = getS3Client();

    /** @var \Aws\Result $responseForMainItem */
    $responseForMainItem = $client->execute($client->getCommand('GetObjectAcl', [
        'Bucket' => getenv('AWS_BUCKET'),
        'Key' => $media->getPath(),
    ]));

    $this->assertEquals('READ', $responseForMainItem->get('Grants')[1]['Permission'] ?? null);

    /** @var \Aws\Result $responseForConversion */
    $responseForConversion = $client->execute($client->getCommand('GetObjectAcl', [
        'Bucket' => getenv('AWS_BUCKET'),
        'Key' => $media->getPath('thumb'),
    ]));

    $this->assertEquals('READ', $responseForConversion->get('Grants')[1]['Permission'] ?? null);
});

test('custom headers are used for all conversions when adding private media from same s3 disk', function () {
    $randomNumber = rand();

    $fileName = "test{$randomNumber}.jpg";

    Storage::disk('s3_disk')->put("tmp/{$fileName}", file_get_contents($this->getTestJpg()));

    $media = $this->testModelWithConversion
        ->addMediaFromDisk("tmp/{$fileName}", 's3_disk')
        ->addCustomHeaders([
            'ACL' => 'public-read',
        ])
        ->toMediaCollection('default', 's3_disk');

    $client = getS3Client();

    /** @var \Aws\Result $responseForMainItem */
    $responseForMainItem = $client->execute($client->getCommand('GetObjectAcl', [
        'Bucket' => getenv('AWS_BUCKET'),
        'Key' => $media->getPath(),
    ]));

    $this->assertEquals('READ', $responseForMainItem->get('Grants')[1]['Permission'] ?? null);

    /** @var \Aws\Result $responseForConversion */
    $responseForConversion = $client->execute($client->getCommand('GetObjectAcl', [
        'Bucket' => getenv('AWS_BUCKET'),
        'Key' => $media->getPath('thumb'),
    ]));

    $this->assertEquals('READ', $responseForConversion->get('Grants')[1]['Permission'] ?? null);
});

test('extra headers are used for all conversions when adding private media from same s3 disk', function () {
    config()->set('media-library.remote.extra_headers', [
        'ACL' => 'public-read',
    ]);

    $randomNumber = random_int(0, mt_getrandmax());

    $fileName = "test{$randomNumber}.jpg";

    Storage::disk('s3_disk')->put("tmp/{$fileName}", file_get_contents($this->getTestJpg()));

    $media = $this->testModelWithConversion
        ->addMediaFromDisk("tmp/{$fileName}", 's3_disk')
        ->toMediaCollection('default', 's3_disk');

    $client = getS3Client();

    /** @var \Aws\Result $responseForMainItem */
    $responseForMainItem = $client->execute($client->getCommand('GetObjectAcl', [
        'Bucket' => getenv('AWS_BUCKET'),
        'Key' => $media->getPath(),
    ]));

    $this->assertEquals('READ', $responseForMainItem->get('Grants')[1]['Permission'] ?? null);

    /** @var \Aws\Result $responseForConversion */
    $responseForConversion = $client->execute($client->getCommand('GetObjectAcl', [
        'Bucket' => getenv('AWS_BUCKET'),
        'Key' => $media->getPath('thumb'),
    ]));

    $this->assertEquals('READ', $responseForConversion->get('Grants')[1]['Permission'] ?? null);
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

    $this->assertSame($existsCreatedAt, Storage::disk('s3_disk')->lastModified($derivedImageExists));
    $this->assertGreaterThan($missingCreatedAt, Storage::disk('s3_disk')->lastModified($derivedMissingImage));
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
    $this->assertSame($existsCreatedAt, Storage::disk('s3_disk')->lastModified($derivedImageExists));
    $this->assertGreaterThan($missingCreatedAt, Storage::disk('s3_disk')->lastModified($derivedMissingImage));
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

    $temporaryDirectory = (new TemporaryDirectory())->create();
    file_put_contents($temporaryDirectory->path('response.zip'), $content);

    $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test.jpg');
});

// Helpers
function cleanUpS3()
{
    collect(Storage::disk('s3_disk')->allDirectories(getS3BaseTestDirectory()))->each(function ($directory) {
        Storage::disk('s3_disk')->deleteDirectory($directory);
    });
}

function getS3Client(): S3Client
{
    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = app(Factory::class)->disk('s3_disk');

    /** @var \Aws\S3\S3Client $client */
    $client = $disk->getDriver()->getAdapter()->getClient();

    return $client;
}

function assertS3FileExists(string $filePath)
{
    test()->assertTrue(Storage::disk('s3_disk')->has($filePath));
}

function assertS3FileNotExists(string $filePath)
{
    test()->assertFalse(Storage::disk('s3_disk')->has($filePath));
}

function canTestS3(): bool
{
    return ! empty(getenv('AWS_ACCESS_KEY_ID'));
}

function getS3BaseTestDirectory(): string
{
    static $uuid = null;

    if (is_null($uuid)) {
        $uuid = Str::uuid();
    }

    return $uuid;
}

function s3BaseUrl(): string
{
    return 'https://laravel-medialibrary-tests.s3.eu-west-1.amazonaws.com';
}
