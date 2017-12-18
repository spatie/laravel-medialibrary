<?php

namespace Spatie\MediaLibrary\Tests\Feature;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\ZipStreamResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipStreamResponseTest extends TestCase
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
        Route::get('stream-test', function () {
            return ZipStreamResponse::create('my-media.zip')->addMedia(Media::all());
        });

        $response = $this->get('stream-test');

        $this->assertInstanceOf(StreamedResponse::class, $response->baseResponse);
    }
}
