<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class GetOriginalUrlAttributeTest extends TestCase
{
    /** @test */
    public function the_original_url_attribute_exists()
    {
        $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertArrayHasKey('original_url', $media->toArray());
    }

    /** @test */
    public function it_can_get_url_of_original_image()
    {
        $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertEquals("/media/{$media->id}/test.jpg", $media->original_url);
    }
}
