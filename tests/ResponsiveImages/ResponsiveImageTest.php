<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

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
            '/media/1/responsive-images/test_medialibrary_original_340.jpg',
            '/media/1/responsive-images/test_medialibrary_original_304.jpg',
            '/media/1/responsive-images/test_medialibrary_original_263.jpg',
            '/media/1/responsive-images/test_medialibrary_original_215.jpg',
            '/media/1/responsive-images/test_medialibrary_original_152.jpg',
        ], $media->getResponsiveImageUrls());

        $this->assertEquals([
            '/media/1/responsive-images/test_thumb_50.jpg',
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

        $this->assertEquals(
            '/media/1/responsive-images/test_medialibrary_original_340.jpg 340w, /media/1/responsive-images/test_medialibrary_original_304.jpg 304w, /media/1/responsive-images/test_medialibrary_original_263.jpg 263w, /media/1/responsive-images/test_medialibrary_original_215.jpg 215w, /media/1/responsive-images/test_medialibrary_original_152.jpg 152w',
             $media->getSrcset()
        );

        $this->assertEquals(
            '/media/1/responsive-images/test_thumb_50.jpg 50w',
             $media->getSrcset('thumb')
        );
    }
}
