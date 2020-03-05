<?php

namespace Spatie\MediaLibrary\Tests\Conversions\ImageGenerators;

use Spatie\MediaLibrary\Conversions\ImageGenerators\Image;
use Spatie\MediaLibrary\Tests\TestCase;

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
