<?php

namespace Spatie\MediaLibrary\Tests\Feature\S3Integration;

use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class S3IntegrationTest extends TestCase
{
    protected string $s3BaseDirectory;

    public function setUp(): void
    {
        parent::setUp();

        if (! $this->canTestS3()) {
            $this->markTestSkipped('Skipping S3 tests because no S3 env variables found');
        }

        $this->s3BaseDirectory = self::getS3BaseTestDirectory();

        $this->app['config']->set('media-library.path_generator', S3TestPathGenerator::class);
    }

    public function tearDown(): void
    {
        $this->cleanUpS3();

        $this->app['config']->set('media-library.path_generator', null);

        parent::tearDown();
    }

    /** @test */
    public function it_can_add_media_from_a_disk_to_s3()
    {
        $randomNumber = rand();

        $fileName = "test{$randomNumber}.jpg";

        Storage::disk('s3_disk')->put("tmp/{$fileName}", file_get_contents($this->getTestJpg()));

        $media = $this->testModel
            ->addMediaFromDisk("tmp/{$fileName}", 's3_disk')
            ->toMediaCollection('default', 's3_disk');

        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/{$fileName}");
    }

    /** @test */
    public function it_can_store_a_file_on_s3()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
    }

    /** @test */
    public function it_can_store_a_file_and_its_conversion_on_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg");
    }

    /** @test */
    public function it_can_delete_a_file_on_s3()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");

        $media->delete();

        $this->assertS3FileNotExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
    }

    /** @test */
    public function it_deletes_file_conversions_on_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg");

        $media->delete();

        $this->assertS3FileNotExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
        $this->assertS3FileNotExists("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg");
    }

    /** @test */
    public function it_retrieve_a_media_url_from_s3()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('default', 's3_disk');

        $this->assertEquals(
            $this->s3BaseUrl()."/{$this->s3BaseDirectory}/{$media->id}/test.jpg",
            $media->getUrl()
        );

        $this->assertEquals(
            sha1(file_get_contents($this->getTestJpg())),
            sha1(file_get_contents($media->getUrl()))
        );
    }

    /** @test */
    public function it_retrieve_a_media_conversion_url_from_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertEquals(
            $this->s3BaseUrl()."/{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg",
            $media->getUrl('thumb')
        );
    }

    /** @test */
    public function it_retrieve_a_media_responsive_urls_from_s3()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection('default', 's3_disk');

        $this->assertEquals([
            $this->s3BaseUrl()."/{$this->s3BaseDirectory}/{$media->id}/responsive-images/test___thumb_50_41.jpg",
        ], $media->getResponsiveImageUrls('thumb'));
    }

    /** @test */
    public function it_retrieves_a_temporary_media_url_from_s3()
    {
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
    }

    /** @test */
    public function it_retrieves_a_temporary_media_url_from_s3_when_s3_root_not_empty()
    {
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
    }

    /** @test */
    public function it_can_get_the_temporary_url_to_first_media_in_a_collection()
    {
        $firstMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
        $firstMedia->save();

        $secondMedia = $this->testModel->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images', 's3_disk');
        $secondMedia->save();

        $this->assertEquals($firstMedia->getTemporaryUrl(Carbon::now()->addMinutes(5)), $this->testModel->getFirstTemporaryUrl(Carbon::now()->addMinutes(5), 'images'));
    }

    /** @test */
    public function it_retrieves_a_temporary_media_conversion_url_from_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertStringContainsString(
            "/{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg",
            $media->getTemporaryUrl(Carbon::now()->addMinutes(5), 'thumb')
        );
    }

    /** @test */
    public function custom_headers_are_used_for_all_conversions()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->addCustomHeaders([
                'ACL' => 'public-read',
            ])
            ->toMediaCollection('default', 's3_disk');

        $client = $this->getS3Client();

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
    }

    /** @test */
    public function it_can_regenerate_only_missing_with_s3_disk()
    {
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

        $this->assertS3FileNotExists($derivedMissingImage);

        sleep(1);

        $this->artisan('media-library:regenerate', [
            '--only-missing' => true,
        ]);

        $this->assertS3FileExists($derivedMissingImage);

        $this->assertSame($existsCreatedAt, Storage::disk('s3_disk')->lastModified($derivedImageExists));
        $this->assertGreaterThan($missingCreatedAt, Storage::disk('s3_disk')->lastModified($derivedMissingImage));
    }

    /** @test */
    public function it_can_regenerate_only_missing_files_of_named_conversions_with_s3_disk()
    {
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

        $this->assertS3FileNotExists($derivedMissingImage);
        $this->assertS3FileNotExists($derivedMissingImageOriginal);

        sleep(1);

        $this->artisan('media-library:regenerate', [
            '--only-missing' => true,
            '--only' => 'thumb',
        ]);

        $this->assertS3FileExists($derivedMissingImage);
        $this->assertS3FileNotExists($derivedMissingImageOriginal);
        $this->assertSame($existsCreatedAt, Storage::disk('s3_disk')->lastModified($derivedImageExists));
        $this->assertGreaterThan($missingCreatedAt, Storage::disk('s3_disk')->lastModified($derivedMissingImage));
    }

    /** @test */
    public function it_can_retrieve_a_zip_with_s3_disk()
    {
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
    }

    protected function cleanUpS3()
    {
        collect(Storage::disk('s3_disk')->allDirectories(self::getS3BaseTestDirectory()))->each(function ($directory) {
            Storage::disk('s3_disk')->deleteDirectory($directory);
        });
    }

    protected function getS3Client(): S3Client
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = app(Factory::class)->disk('s3_disk');

        /** @var \Aws\S3\S3Client $client */
        $client = $disk->getDriver()->getAdapter()->getClient();

        return $client;
    }

    protected function assertS3FileExists(string $filePath)
    {
        $this->assertTrue(Storage::disk('s3_disk')->has($filePath));
    }

    protected function assertS3FileNotExists(string $filePath)
    {
        $this->assertFalse(Storage::disk('s3_disk')->has($filePath));
    }

    public function canTestS3()
    {
        return ! empty(getenv('AWS_ACCESS_KEY_ID'));
    }

    public static function getS3BaseTestDirectory(): string
    {
        static $uuid = null;

        if (is_null($uuid)) {
            $uuid = Str::uuid();
        }

        return $uuid;
    }

    public function s3BaseUrl(): string
    {
        return 'https://laravel-medialibrary-tests.s3.eu-west-1.amazonaws.com';
    }
}
