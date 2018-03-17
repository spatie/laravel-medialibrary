<?php

namespace Spatie\MediaLibrary\Tests\Feature\ResponsiveImages;

use Spatie\MediaLibrary\Tests\TestCase;

class RegisteredResponsiveImagesTest extends TestCase
{
    /** @test */
    public function it_will_register_generated_responsive_images_in_the_db()
    {
        $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

        $media = $this->testModel->getFirstMedia();

        $this->assertEquals([
            'test___medialibrary_original_340_280.jpg',
            'test___medialibrary_original_284_233.jpg',
            'test___medialibrary_original_237_195.jpg',
        ], $media->responsive_images['medialibrary_original']['urls']);
    }
}
