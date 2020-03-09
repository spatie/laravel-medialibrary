<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class ResponsableTest extends TestCase
{
    /** @test */
    public function it_can_return_an_image_as_a_response()
    {
        $this->app['router']->get('/upload', fn () => $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection());

        $result = $this->call('get', 'upload');

        $this->assertEquals(200, $result->getStatusCode());
        $result->assertHeader('Content-Type', 'image/jpeg');
        $result->assertHeader('Content-Length', 29085);
    }

    /** @test */
    public function it_can_return_a_text_as_a_response()
    {
        $this->app['router']->get('/upload', fn () => $this->testModel
            ->addMedia($this->getTestFilesDirectory('test.txt'))
            ->toMediaCollection());

        $result = $this->call('get', 'upload');

        $this->assertEquals(200, $result->getStatusCode());
        $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $result->assertHeader('Content-Length', 45);
    }
}
