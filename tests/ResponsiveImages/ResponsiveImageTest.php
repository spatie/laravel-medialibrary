<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

class ResponsiveImageTest extends TestCase
{
    /** @test */
    public function a_media_instance_can_return_properties_of_responsive_images()
    {
        $this->testModelWithoutMediaConversions
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModelWithoutMediaConversions->getFirstMedia();

        $responsiveImage = $media->responsiveImages()->first();
    
        $this->assertInstanceOf(ResponsiveImage::class, $responsiveImage);

        $this->assertEquals('medialibrary_original', $responsiveImage->generatedFor());

        $this->assertEquals(340, $responsiveImage->width());

        $this->assertEquals('/media/1/responsive-images/test_medialibrary_original_340.jpg', $responsiveImage->url());
    }

    /** @test */
    public function a_media_instance_can_return_properties_of_responsive_images_for_converted_images()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $responsiveImage = $media->responsiveImages()->first();
    
        $this->assertInstanceOf(ResponsiveImage::class, $responsiveImage);

        $this->assertEquals('thumb', $responsiveImage->generatedFor());

        $this->assertEquals(50, $responsiveImage->width());

        $this->assertEquals('/media/1/responsive-images/test_thumb_50.jpg', $responsiveImage->url());
    }

    /** @test */
    public function a_media_instance_has_a_shorthand_method_for_getting_responsive_image_urls()
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
            '/media/1/responsive-images/test_thumb_44.jpg',
            '/media/1/responsive-images/test_thumb_38.jpg',
            '/media/1/responsive-images/test_thumb_31.jpg',
            '/media/1/responsive-images/test_thumb_22.jpg',
        ], $media->getResponsiveImageUrls('thumb'));

        $this->assertEquals([], $media->getResponsiveImageUrls('non-existing-conversion'));
    }

    /** @test */
    public function a_media_instance_can_determine_the_contents_of_scrset()
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
            '/media/1/responsive-images/test_thumb_50.jpg 50w, /media/1/responsive-images/test_thumb_44.jpg 44w, /media/1/responsive-images/test_thumb_38.jpg 38w, /media/1/responsive-images/test_thumb_31.jpg 31w, /media/1/responsive-images/test_thumb_22.jpg 22w',
             $media->getSrcset('thumb')
        );
    }
}
