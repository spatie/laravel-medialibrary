<?php

namespace Spatie\MediaLibrary\Tests\MediaCollections\FileSystem;

use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\Tests\TestCase;

class FileSystemTest extends TestCase
{
    protected Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->app->make(Filesystem::class);
    }

    /** @test */
    public function it_can_determine_the_header_for_file_that_will_be_copied_to_an_external_filesytem()
    {
        $expectedHeaders = [
            'ContentType' => 'image/jpeg',
            'CacheControl' => 'max-age=604800',
        ];

        $this->assertEquals(
            $expectedHeaders,
            $this->filesystem->getRemoteHeadersForFile($this->getTestJpg())
        );
    }

    /** @test */
    public function it_can_add_custom_headers_for_file_that_will_be_copied_to_an_external_filesytem()
    {
        $this->filesystem->addCustomRemoteHeaders([
            'ACL' => 'public-read',
        ]);

        $expectedHeaders = [
            'ContentType' => 'image/jpeg',
            'CacheControl' => 'max-age=604800',
            'ACL' => 'public-read',
        ];

        $this->assertEquals(
            $expectedHeaders,
            $this->filesystem->getRemoteHeadersForFile($this->getTestJpg())
        );
    }

    /** @test */
    public function it_can_use_custom_headers_when_copying_the_media_to_an_external_filesystem()
    {
        $this->filesystem->addCustomRemoteHeaders([
            'CacheControl' => 'max-age=302400',
        ]);

        $expectedHeaders = [
            'ContentType' => 'image/jpeg',
            'CacheControl' => 'max-age=302400',
        ];

        $this->assertEquals(
            $expectedHeaders,
            $this->filesystem->getRemoteHeadersForFile($this->getTestJpg())
        );
    }
}
