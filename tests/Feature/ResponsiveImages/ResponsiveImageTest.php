<?php

namespace Spatie\MediaLibrary\Tests\Feature\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;

class ResponsiveImageTest extends TestCase
{
    /** @test */
    public function a_media_instance_can_get_responsive_image_urls()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $this->assertEquals([
            'http://localhost/media/1/responsive-images/test___medialibrary_original_340_280.jpg',
            'http://localhost/media/1/responsive-images/test___medialibrary_original_284_233.jpg',
            'http://localhost/media/1/responsive-images/test___medialibrary_original_237_195.jpg',
        ], $media->getResponsiveImageUrls());

        $this->assertEquals([
            'http://localhost/media/1/responsive-images/test___thumb_50_41.jpg',
        ], $media->getResponsiveImageUrls('thumb'));

        $this->assertEquals([], $media->getResponsiveImageUrls('non-existing-conversion'));
    }

    /** @test */
    public function a_media_instance_can_generate_the_contents_of_scrset()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $this->assertContains(
            'http://localhost/media/1/responsive-images/test___medialibrary_original_340_280.jpg 340w, http://localhost/media/1/responsive-images/test___medialibrary_original_284_233.jpg 284w, http://localhost/media/1/responsive-images/test___medialibrary_original_237_195.jpg 237w',
             $media->getSrcset()
        );
        $this->assertContains('data:image/svg+xml;base64', $media->getSrcset());

        $this->assertContains(
            'http://localhost/media/1/responsive-images/test___thumb_50_41.jpg 50w',
             $media->getSrcset('thumb')
        );
        $this->assertContains('data:image/svg+xml;base64,', $media->getSrcset('thumb'));
    }

    /** @test */
    public function a_responsive_image_can_return_some_properties()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModel->getFirstMedia();

        $responsiveImage = $media->responsiveImages()->files->first();

        $this->assertEquals('medialibrary_original', $responsiveImage->generatedFor());

        $this->assertEquals(340, $responsiveImage->width());

        $this->assertEquals(280, $responsiveImage->height());
    }
}
