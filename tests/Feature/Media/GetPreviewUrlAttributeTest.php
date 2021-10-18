<?php

namespace Spatie\MediaLibrary\Tests\Feature\Media;

use Spatie\MediaLibrary\Tests\TestCase;

class GetPreviewUrlAttributeTest extends TestCase
{
    /** @test */
    public function the_preview_url_attribute_exists()
    {
        $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertArrayHasKey('preview_url', $media->toArray());
    }

    /** @test */
    public function it_can_get_url_of_preview_image()
    {
        $media = $this->testModelWithPreviewConversion->addMedia($this->getTestJpg())->toMediaCollection();

        $conversionName = 'preview';

        $this->assertEquals("/media/{$media->id}/conversions/test-{$conversionName}.jpg", $media->preview_url);
    }
}
