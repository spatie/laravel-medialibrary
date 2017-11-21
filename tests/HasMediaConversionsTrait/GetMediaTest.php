<?php

namespace Spatie\MediaLibrary\Tests\HasMediaConversionsTrait;

use Spatie\MediaLibrary\Tests\TestCase;

class GetMediaTest extends TestCase
{
    /** @test */
    public function it_can_handle_an_empty_collection()
    {
        $emptyCollection = $this->testModelWithoutMediaConversions->getMedia('images');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $emptyCollection);
        $this->assertCount(0, $emptyCollection);
    }
}
