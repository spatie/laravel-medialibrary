<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class CustomHeadersTest extends TestCase
{
    /** @test */
    public function it_does_not_set_empty_custom_headers_when_saved()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $this->assertFalse($media->hasCustomProperty('custom_headers'));
        $this->assertEquals([], $media->getCustomHeaders());
    }

    /** @test */
    public function it_can_set_and_retrieve_custom_headers_when_explicitly_added()
    {
        $headers = [
            'Header' => 'Present',
        ];

        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->toMediaCollection()
            ->setCustomHeaders($headers);

        $this->assertTrue($media->hasCustomProperty('custom_headers'));
        $this->assertEquals($headers, $media->getCustomHeaders());
    }
}
