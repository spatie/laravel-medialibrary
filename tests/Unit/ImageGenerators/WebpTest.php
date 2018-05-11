<?php

namespace Spatie\MediaLibrary\Tests\Unit\ImageGenerators;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Webp;

class WebpTest extends TestCase
{
    /** @test */
    public function it_can_convert_an_image()
    {
        $imageGenerator = new Webp();

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWebp())->toMediaCollection();

        $this->assertTrue($imageGenerator->canConvert($media));

        $imageFile = $imageGenerator->convert($media->getPath());

        $this->assertEquals('image/png', mime_content_type($imageFile));
    }
}
