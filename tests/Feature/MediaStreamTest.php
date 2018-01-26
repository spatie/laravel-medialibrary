<?php

namespace Spatie\MediaLibrary\Tests\Feature;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\MediaStream;
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
}
