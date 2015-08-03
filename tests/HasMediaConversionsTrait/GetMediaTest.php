<?php

namespace Spatie\MediaLibrary\Test\HasMediaConversionsTrait;

use Spatie\MediaLibrary\Test\TestCase;

class GetMediaTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_an_empty_collection()
    {
        $emptyCollection = $this->testModelWithoutMediaConversions->getMedia('images');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $emptyCollection);
        $this->assertCount(0, $emptyCollection);
    }
}
