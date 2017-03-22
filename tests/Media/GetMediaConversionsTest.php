<?php

namespace Spatie\MediaLibrary\Test\Media;

use Spatie\MediaLibrary\Test\TestCase;

class GetMediaConversionsTest extends TestCase
{
    /** @test */
    public function it_can_get_the_names_of_registered_conversions()
    {
        $media = $this->testModel
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $this->assertSame([], $media->getMediaConversionNames());

        $media = $this->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $this->assertSame(['thumb'], $media->getMediaConversionNames());
    }
}
