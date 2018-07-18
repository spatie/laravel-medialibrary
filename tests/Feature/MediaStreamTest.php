<?php

namespace Spatie\MediaLibrary\Tests\Feature;

use ZipArchive;
use Spatie\MediaLibrary\MediaStream;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStreamTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        foreach (range(1, 3) as $i) {
            $this->testModel
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->toMediaCollection();
        }
    }

    /** @test */
    public function it_can_return_a_stream_of_media()
    {
        $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

        $this->assertEquals(count(Media::all()), $zipStreamResponse->getMediaItems()->count());

        Route::get('stream-test', function () use ($zipStreamResponse) {
            return $zipStreamResponse;
        });

        $response = $this->get('stream-test');

        $this->assertInstanceOf(StreamedResponse::class, $response->baseResponse);
    }

    /** @test */
    public function it_can_return_a_stream_of_multiple_files_with_the_same_filename()
    {
        $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

        ob_start();
        @$zipStreamResponse->toResponse(request())->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $temporaryDirectory = (new TemporaryDirectory())->create();
        file_put_contents($temporaryDirectory->path('response.zip'), $content);

        $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test.jpg');
        $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test (1).jpg');
        $this->assertFileExistsInZip($temporaryDirectory->path('response.zip'), 'test (2).jpg');
    }

    /** @test */
    public function media_can_be_added_to_it_one_by_one()
    {
        $zipStreamResponse = MediaStream::create('my-media.zip')
            ->addMedia(Media::find(1))
            ->addMedia(Media::find(2));

        $this->assertEquals(2, $zipStreamResponse->getMediaItems()->count());
    }

    /** @test */
    public function an_array_of_media_can_be_added_to_it()
    {
        $zipStreamResponse = MediaStream::create('my-media.zip')
            ->addMedia([Media::find(1), Media::find(2)]);

        $this->assertEquals(2, $zipStreamResponse->getMediaItems()->count());
    }

    protected function assertFileExistsInZip($zipPath, $filename)
    {
        $this->assertTrue($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} contains a file name {$filename}");
    }

    protected function assertFileDoesntExistsInZip($zipPath, $filename)
    {
        $this->assertFalse($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} doesn't contain a file name {$filename}");
    }

    protected function fileExistsInZip($zipPath, $filename): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) === true) {
            return $zip->locateName($filename, ZipArchive::FL_NODIR) !== false;
        }

        return false;
    }
}
