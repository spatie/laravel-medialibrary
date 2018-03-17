<?php

namespace Spatie\MediaLibrary\Tests\Feature\Commands;

use Illuminate\Support\Facades\Artisan;
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

        Artisan::call('medialibrary:clean');

        $media->refresh();

        $this->assertEquals($originalResponsiveImagesContent, $media->responsive_images);
        $this->assertFileNotExists($deprecatedReponsiveImagesPath);
    }
}
