<?php

namespace Spatie\MediaLibrary\Tests\Feature\S3Integration;

use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Tests\TestCase;

class S3IntegrationTest extends TestCase
{
    /** @var @string */
    protected $s3BaseDirectory;

    public function setUp()
    {
        parent::setUp();

        if (! $this->canTestS3()) {
            $this->markTestSkipped('Skipping S3 tests because no S3 env variables found');
        }

        $this->s3BaseDirectory = self::getS3BaseTestDirectory();

        $this->app['config']->set('medialibrary.path_generator', S3TestPathGenerator::class);
    }

    public function tearDown()
    {
        $this->cleanUpS3();

        $this->app['config']->set('medialibrary.path_generator', null);

        parent::tearDown();
    }

    /** @test */
    public function it_can_store_a_file_on_s3()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertTrue(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/test.jpg"));
    }

    /** @test */
    public function it_can_store_a_file_and_its_conversion_on_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertTrue(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/test.jpg"));
        $this->assertTrue(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg"));
    }

    /** @test */
    public function it_can_delete_a_file_on_s3()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertTrue(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/test.jpg"));

        $media->delete();

        $this->assertFalse(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/test.jpg"));
    }

    /** @test */
    public function it_deletes_file_converions_on_s3()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection('default', 's3_disk');

        $this->assertTrue(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/test.jpg"));
        $this->assertTrue(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg"));

        $media->delete();

        $this->assertFalse(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/test.jpg"));
        $this->assertFalse(Storage::disk('s3_disk')->has("{$this->s3BaseDirectory}/{$media->id}/conversions/test-thumb.jpg"));
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
    public function it_retrieves_a_temporary_media_url_from_s3()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection('default', 's3_disk');

        $this->assertContains(
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

        $this->assertContains(
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

        /** @var \Aws\Result $responseForConversion**/
        $responseForConversion = $client->execute($client->getCommand('GetObjectAcl', [
            'Bucket' => getenv('AWS_BUCKET'),
            'Key' => $media->getPath('thumb'),
        ]));

        $this->assertEquals('READ', $responseForConversion->get('Grants')[1]['Permission'] ?? null);
    }

    protected function cleanUpS3()
    {
        collect(Storage::disk('s3_disk')->allDirectories(self::getS3BaseTestDirectory()))->each(function ($directory) {
            Storage::disk('s3_disk')->deleteDirectory($directory);
        });
    }

    public function canTestS3()
    {
        return ! empty(getenv('AWS_KEY'));
    }

    protected function getS3Client(): S3Client
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = app(Factory::class)->disk('s3_disk');

        /** @var \Aws\S3\S3Client $client */
        $client = $disk->getDriver()->getAdapter()->getClient();

        return $client;
    }

    public static function getS3BaseTestDirectory(): string
    {
        return md5(getenv('TRAVIS_BUILD_ID').app()->version().phpversion());
    }
}
