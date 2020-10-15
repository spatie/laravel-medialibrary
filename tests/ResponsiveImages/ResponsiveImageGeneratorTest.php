<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGenerated;
use Spatie\MediaLibrary\Tests\TestCase;

class ResponsiveImageGeneratorTest extends TestCase
{
    public string $fileName = "test";

    /** @test */
    public function it_can_generate_responsive_images()
    {
        $this->testModel
                ->addMedia($this->getTestJpg())
                ->withResponsiveImages()
                ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"));
        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg"));
        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg"));
    }

    /** @test */
    public function it_will_generate_responsive_images_if_withResponsiveImagesIf_returns_true()
    {
        $this->testModel
                ->addMedia($this->getTestJpg())
                ->withResponsiveImagesIf(fn () => true)
                ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"));
        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg"));
        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg"));
    }

    /** @test */
    public function it_will_not_generate_responsive_images_if_withResponsiveImagesIf_returns_false()
    {
        $this->testModel
                ->addMedia($this->getTestJpg())
                ->withResponsiveImagesIf(fn () => false)
                ->toMediaCollection();

        $this->assertFileDoesNotExist($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"));
    }

    /** @test */
    public function its_conversions_can_have_responsive_images()
    {
        $this->testModelWithResponsiveImages
                    ->addMedia($this->getTestJpg())
                    ->withResponsiveImages()
                    ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg"));
    }

    /** @test */
    public function its_conversions_can_have_responsive_images_and_change_format()
    {
        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestPng())
            ->withResponsiveImages()
            ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___pngtojpg_700_883.jpg"));
    }

    /** @test */
    public function it_triggers_an_event_when_the_responsive_images_are_generated()
    {
        Event::fake(ResponsiveImagesGenerated::class);

        $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        Event::assertDispatched(ResponsiveImagesGenerated::class);
    }

    /** @test */
    public function it_cleans_the_responsive_images_urls_from_the_db_before_regeneration()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestFilesDirectory("test.jpg"))
            ->withResponsiveImages()
            ->toMediaCollection();

        $this->assertCount(1, $media->fresh()->responsive_images["thumb"]["urls"]);

        $this->artisan("media-library:regenerate");
        $this->assertCount(1, $media->fresh()->responsive_images["thumb"]["urls"]);
    }

    /** @test */
    public function it_will_add_responsive_image_entries_when_there_were_none_when_regenerating()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestFilesDirectory("test.jpg"))
            ->withResponsiveImages()
            ->toMediaCollection();

        // remove all responsive image db entries
        $responsiveImages = $media->responsive_images;
        $responsiveImages["thumb"]["urls"] = [];
        $media->responsive_images = $responsiveImages;
        $media->save();
        $this->assertCount(0, $media->fresh()->responsive_images["thumb"]["urls"]);

        $this->artisan("media-library:regenerate");
        $this->assertCount(1, $media->fresh()->responsive_images["thumb"]["urls"]);
    }
}
