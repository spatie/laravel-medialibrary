<?php

namespace Spatie\MediaLibrary\Tests\Feature\Models\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class ResponsableTest extends TestCase
{
    /** @test */
    public function it_can_return_an_image_as_a_response()
    {
        $this->withoutExceptionHandling();

        $this->app['router']->get('/upload', function () {
            return $this->testModel
                ->addMedia($this->getTestJpg())
                ->toMediaCollection();
        });

        $result = $this->call('get', 'upload');

        $this->assertEquals(200, $result->getStatusCode());
        $result->assertHeader('Content-Type', 'image/jpeg');
        $this->assertEquals(29085, $result->baseResponse->getFile()->getSize());
    }

    /** @test */
    public function it_can_return_a_text_as_a_response()
    {
        $this->app['router']->get('/upload', function () {
            return $this->testModel
                ->addMedia($this->getTestFilesDirectory('test.txt'))
                ->toMediaCollection();
        });

        $result = $this->call('get', 'upload');

        $this->assertEquals(200, $result->getStatusCode());
        $result->assertHeader('Content-Type', 'text/plain');
        $this->assertEquals(45, $result->baseResponse->getFile()->getSize());
    }
}
