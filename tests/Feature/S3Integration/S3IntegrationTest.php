<?php

namespace Spatie\MediaLibrary\Tests\Feature\S3Integration;

use Carbon\Carbon;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Tests\TestCase;
use Illuminate\Contracts\Filesystem\Factory;

class S3IntegrationTest extends TestCase
{
    /** @var @string */
    protected $s3BaseDirectory;

    public function setUp(): void
    {
        parent::setUp();

        if (! $this->canTestS3()) {
            $this->markTestSkipped('Skipping S3 tests because no S3 env variables found');
        }

        $this->s3BaseDirectory = self::getS3BaseTestDirectory();

        $this->app['config']->set('medialibrary.path_generator', S3TestPathGenerator::class);
    }

    public function tearDown(): void
    {
        $this->cleanUpS3();

        $this->app['config']->set('medialibrary.path_generator', null);

        parent::tearDown();
    }

    /** @test */
    public function it_can_add_media_from_a_disk_to_s3()
    {
        Storage::disk('s3_disk')->put('tmp/test.jpg', file_get_contents($this->getTestJpg()));

        $media = $this->testModel
            ->addMediaFromDisk('tmp/test.jpg', 's3_disk')
            ->toMediaCollection('default', 's3_disk');

        $this->assertS3FileExists("{$this->s3BaseDirectory}/{$media->id}/test.jpg");
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
            $this->app['config']->get('medialibrary.s3.domain')."/{$this->s3BaseDirectory}/{$media->id}/test.jpg",
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
            $this->app['config']->get('medialibrary.s3.domain')."/{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg",
            $media->getUrl('thumb')
        );
    }

    /** @test */
    public function it_retrieve_a_media_responsive_url_from_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertEquals(
            $this->app['config']->get('medialibrary.s3.domain')."/{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg",
            $media->getResponsiveImagesDirectoryUrl('thumb')
        );
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

        Artisan::call('medialibrary:regenerate', [
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

        Artisan::call('medialibrary:regenerate', [
            '--only-missing' => true,
            '--only' => 'thumb',
        ]);

        $this->assertS3FileExists($derivedMissingImage);
        $this->assertS3FileNotExists($derivedMissingImageOriginal);
        $this->assertSame($existsCreatedAt, Storage::disk('s3_disk')->lastModified($derivedImageExists));
        $this->assertGreaterThan($missingCreatedAt, Storage::disk('s3_disk')->lastModified($derivedMissingImage));
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
        return ! empty(getenv('AWS_KEY'));
    }

    public static function getS3BaseTestDirectory(): string
    {
        return md5(getenv('TRAVIS_BUILD_ID').app()->version().phpversion());
    }
}
