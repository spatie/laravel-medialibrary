<?php

namespace Spatie\MediaLibrary\Tests\Conversions\Commands\CleanCommandTest;

use Spatie\MediaLibrary\Tests\TestCase;

class CleanResponsiveImagesTest extends TestCase
{
    /** @test */
    public function it_can_clean_responsive_images()
    {
        $media = $this->testModelWithResponsiveImages
            ->addMedia($this->getTestJpg())
            ->preservingOriginal()
            ->toMediaCollection();

        $deprecatedResponsiveImageFileName = 'test___deprecatedConversion_50_41.jpg';
        $deprecatedReponsiveImagesPath = $this->getMediaDirectory("1/responsive-images/{$deprecatedResponsiveImageFileName}");
        touch($deprecatedReponsiveImagesPath);

        $originalResponsiveImagesContent = $media->responsive_images;
        $newResponsiveImages = $originalResponsiveImagesContent;
        $newResponsiveImages['deprecatedConversion'] = $originalResponsiveImagesContent['thumb'];
        $newResponsiveImages['deprecatedConversion']['urls'][0] = $deprecatedResponsiveImageFileName;
        $media->responsive_images = $newResponsiveImages;
        $media->save();

        $this->artisan('media-library:clean');

        $media->refresh();

        $this->assertEquals($originalResponsiveImagesContent, $media->responsive_images);
        $this->assertFileDoesNotExist($deprecatedReponsiveImagesPath);
    }
}
