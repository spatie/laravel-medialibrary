<?php

namespace Spatie\MediaLibrary\Test\FileSystem;

use Spatie\MediaLibrary\Filesystem;
use Spatie\MediaLibrary\Test\TestCase;

class FilesystemTest extends TestCase
{
    /**
     * @var \Spatie\MediaLibrary\Filesystem
     */
    protected $filesystem;

    public function setUp()
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
}
