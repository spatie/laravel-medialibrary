<?php

namespace Spatie\MediaLibrary\Tests\Feature\ResponsiveImages;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Events\ResponsiveImagesGenerated;

class ResponsiveImageGeneratorTest extends TestCase
{
    /** @test */
    public function it_can_generate_responsive_images()
    {
        $this->testModel
                ->addMedia($this->getTestJpg())
                ->withResponsiveImages()
                ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test___medialibrary_original_237_195.jpg'));
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test___medialibrary_original_284_233.jpg'));
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test___medialibrary_original_340_280.jpg'));
    }

    /** @test */
    public function its_conversions_can_have_responsive_images()
    {
        $this->testModelWithResponsiveImages
                    ->addMedia($this->getTestJpg())
                    ->withResponsiveImages()
                    ->toMediaCollection();

        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test___thumb_50_41.jpg'));
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
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->withResponsiveImages()
            ->toMediaCollection();

        $this->assertCount(1, $media->fresh()->responsive_images['thumb']['urls']);

        Artisan::call('medialibrary:regenerate');
        $this->assertCount(1, $media->fresh()->responsive_images['thumb']['urls']);
    }

    /** @test */
    public function it_will_add_responsive_image_entries_when_there_were_none_when_regenerating()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->withResponsiveImages()
            ->toMediaCollection();

        // remove all responsive image db entries
        $responsiveImages = $media->responsive_images;
        $responsiveImages['thumb']['urls'] = [];
        $media->responsive_images = $responsiveImages;
        $media->save();
        $this->assertCount(0, $media->fresh()->responsive_images['thumb']['urls']);

        Artisan::call('medialibrary:regenerate');
        $this->assertCount(1, $media->fresh()->responsive_images['thumb']['urls']);
    }
}
