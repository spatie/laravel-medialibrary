<?php

namespace Spatie\MediaLibrary\Tests\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;

class ResponsiveImagesTest extends TestCase
{
    /** @test */
    public function it_will_register_generated_responsive_images_in_the_db()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModel->getFirstMedia();

        $this->assertEquals($media->responsive_images, [
            0 => 'test_medialibrary_original_340.jpg',
            1 => 'test_medialibrary_original_304.jpg',
            2 => 'test_medialibrary_original_263.jpg',
            3 => 'test_medialibrary_original_215.jpg',
            4 => 'test_medialibrary_original_152.jpg',
        ]);
    }
}
