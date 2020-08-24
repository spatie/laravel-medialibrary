<?php

namespace Spatie\MediaLibrary\Tests\Support;

use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStreamTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        foreach (range(1, 3) as $i) {
            $this
                ->testModel
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

        Route::get('stream-test', fn () => $zipStreamResponse);

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

    /** @test */
    public function media_with_zip_file_folder_prefix_property_saved_in_correct_zip_folder()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties([
                'zip_filename_prefix' => 'folder/subfolder/',
            ])
            ->toMediaCollection();

        $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

        ob_start();
        @$zipStreamResponse->toResponse(request())->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $temporaryDirectory = (new TemporaryDirectory())->create();
        file_put_contents($temporaryDirectory->path('response.zip'), $content);

        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test (2).jpg');

        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'folder/subfolder/test.jpg');
    }

    public function media_with_zip_file_folder_prefix_property_saved_in_correct_zip_folder_and_correct_suffix()
    {
        foreach (range(1, 2) as $i) {
            $this->testModel
                ->addMedia($this->getTestJpg())
                ->preservingOriginal()
                ->withCustomProperties([
                    'zip_filename_prefix' => 'folder/subfolder/',
                ])
                ->toMediaCollection();
        }

        $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

        ob_start();
        @$zipStreamResponse->toResponse(request())->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $temporaryDirectory = (new TemporaryDirectory())->create();
        file_put_contents($temporaryDirectory->path('response.zip'), $content);

        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test.jpg');
        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test (2).jpg');
        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'test (3).jpg');

        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'folder/subfolder/test.jpg');
        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'folder/subfolder/test (2).jpg');
    }

    /** @test */
    public function media_with_zip_file_prefix_property_saved_with_correct_prefix()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->withCustomProperties([
                'zip_filename_prefix' => 'just_a_string_prefix ',
            ])
            ->toMediaCollection();

        $zipStreamResponse = MediaStream::create('my-media.zip')->addMedia(Media::all());

        ob_start();
        @$zipStreamResponse->toResponse(request())->sendContent();
        $content = ob_get_contents();
        ob_end_clean();

        $temporaryDirectory = (new TemporaryDirectory())->create();
        file_put_contents($temporaryDirectory->path('response.zip'), $content);

        $this->assertFileExistsInZipRecognizeFolder($temporaryDirectory->path('response.zip'), 'just_a_string_prefix test.jpg');
    }
}
