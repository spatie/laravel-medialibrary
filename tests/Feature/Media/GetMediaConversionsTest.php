<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

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

        $this->assertSame(['thumb', 'keep_original_format'], $media->getMediaConversionNames());
    }
}
