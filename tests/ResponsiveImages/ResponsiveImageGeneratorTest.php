<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;

class ResponsiveImageGeneratorTest extends TestCase
{
    /** @test */
    public function it_can_generate_responsive_images()
    {
        $this->testModel
                ->addMedia($this->getTestJpg())
                ->withResponsiveImages()
                ->toMediaCollection();
    
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test_medialibrary_original_152.jpg'));
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test_medialibrary_original_215.jpg'));
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test_medialibrary_original_263.jpg'));
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test_medialibrary_original_304.jpg'));
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test_medialibrary_original_340.jpg'));
    }
    
    /** @test */
    public function its_conversions_can_have_responsive_images()
    {
        $this->testModelWithResponsiveImages
                    ->addMedia($this->getTestJpg())
                    ->withResponsiveImages()
                    ->toMediaCollection();
        
        $this->assertFileExists($this->getTempDirectory('media/1/responsive-images/test_thumb_50.jpg'));
    }
}
