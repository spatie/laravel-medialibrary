<?php

namespace Spatie\MediaLibrary\Test\ImageGenerators;

use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Image;

class ImageTest extends TestCase
{
    /** @test */
    public function it_can_convert_an_image()
    {
        $imageGenerator = new Image();

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestJpg())->toMediaCollection();

        $this->assertTrue($imageGenerator->canConvert($media));

        $imageFile = $imageGenerator->convert($media->getPath());

        $this->assertEquals('image/jpeg', mime_content_type($imageFile));
        $this->assertEquals($imageFile, $media->getPath());
    }
}
