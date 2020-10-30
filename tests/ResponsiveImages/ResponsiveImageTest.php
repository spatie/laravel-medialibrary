<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;

class ResponsiveImageTest extends TestCase
{
    public string $fileName = 'test';
    public string $fileNameWithUnderscore = 'test_';

    /** @test */
    public function a_media_instance_can_get_responsive_image_urls()
    {
        $this
            ->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $this->assertEquals([
            "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg",
            "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg",
            "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg",
        ], $media->getResponsiveImageUrls());

        $this->assertEquals([
            "http://localhost/media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg",
        ], $media->getResponsiveImageUrls("thumb"));

        $this->assertEquals([], $media->getResponsiveImageUrls("non-existing-conversion"));
    }

    /** @test */
    public function a_media_instance_can_generate_the_contents_of_scrset()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $this->assertStringContainsString(
            "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg 340w, http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg 284w, http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg 237w",
            $media->getSrcset()
        );
        $this->assertStringContainsString("data:image/svg+xml;base64", $media->getSrcset());

        $this->assertStringContainsString(
            "http://localhost/media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg 50w",
            $media->getSrcset("thumb")
        );
        $this->assertStringContainsString("data:image/svg+xml;base64,", $media->getSrcset("thumb"));
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

        $this->assertEquals("media_library_original", $responsiveImage->generatedFor());

        $this->assertEquals(340, $responsiveImage->width());

        $this->assertEquals(280, $responsiveImage->height());
    }

    /** @test */
    public function responsive_image_generation_respects_the_conversion_quality_setting()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection("default");

        $standardQualityResponsiveConversion = $this->getTempDirectory("media/1/responsive-images/{$this->fileName}___standardQuality_340_280.jpg");
        $lowerQualityResponsiveConversion = $this->getTempDirectory("media/1/responsive-images/{$this->fileName}___lowerQuality_340_280.jpg");

        $this->assertLessThan(filesize($standardQualityResponsiveConversion), filesize($lowerQualityResponsiveConversion));
    }

    /** @test */
    public function a_media_instance_can_get_responsive_image_urls_with_conversions_stored_on_second_media_disk()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->storingConversionsOnDisk("secondMediaDisk")
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $this->assertEquals([
            "http://localhost/media2/1/responsive-images/{$this->fileName}___thumb_50_41.jpg",
        ], $media->getResponsiveImageUrls("thumb"));
    }

    /** @test  */
    public function it_can_handle_file_names_with_underscore()
    {
        $this
            ->testModelWithResponsiveImages
            ->addMedia($this->getTestImageEndingWithUnderscore())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModelWithResponsiveImages->getFirstMedia();

        $this->assertSame([
            "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_340_280.jpg",
            "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_284_233.jpg",
            "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_237_195.jpg",
        ], $media->getResponsiveImageUrls());

        $this->assertSame([
            "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___thumb_50_41.jpg",
        ], $media->getResponsiveImageUrls("thumb"));

        $this->assertSame([], $media->getResponsiveImageUrls("non-existing-conversion"));
    }
}
