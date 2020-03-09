<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\ResponsiveImages\RegisteredResponsiveImages;
use Spatie\MediaLibrary\Tests\TestCase;

class RegisteredResponsiveImagesTest extends TestCase
{
    /** @test */
    public function it_will_register_generated_responsive_images_in_the_db()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModel->getFirstMedia();

        $this->assertEquals([
            'test___media_library_original_340_280.jpg',
            'test___media_library_original_284_233.jpg',
            'test___media_library_original_237_195.jpg',
        ], $media->responsive_images['media_library_original']['urls']);
    }

    /** @test */
    public function it_can_render_a_srcset_when_the_base64svg_is_not_rendered_yet()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModel->getFirstMedia();

        $responsiveImages = $media->responsive_images;

        unset($responsiveImages['media_library_original']['base64svg']);

        $media->responsive_images = $responsiveImages;

        $registeredResponsiveImage = new RegisteredResponsiveImages($media);

        $this->assertNull($registeredResponsiveImage->getPlaceholderSvg());

        $this->assertNotEmpty($registeredResponsiveImage->getSrcset());
    }
}
