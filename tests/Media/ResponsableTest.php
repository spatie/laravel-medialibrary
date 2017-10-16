<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class ResponsableTest extends TestCase
{
    /** @test */
    public function it_can_be_returned_as_a_response()
    {
        $this->app['router']->get('/upload', function () {
            return $this->testModel
                ->addMedia($this->getTestJpg())
                ->usingFileName('othertest.jpg')
                ->toMediaCollection();
        });

        $result = $this->call('get', 'upload');

        $this->assertEquals(200, $result->getStatusCode());
        $result->assertHeader('Content-Type', 'image/jpeg');
        $this->assertEquals(29085, $result->baseResponse->getFile()->getSize());
    }
}