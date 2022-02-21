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

    /** @test */
    public function it_can_convert_a_tiff_image()
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped(
                'The imagick extension is not available.'
            );
        }

        //TIFF format requires imagick
        config(['media-library.image_driver' => 'imagick']);

        $imageGenerator = new Image();

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestTiff())->toMediaCollection();

        $this->assertTrue($imageGenerator->canConvert($media));

        $imageFile = $imageGenerator->convert($media->getPath());

        $this->assertEquals('image/tiff', mime_content_type($imageFile));
        $this->assertEquals($imageFile, $media->getPath());
    }
}
