<?php

namespace Spatie\MediaLibrary\Test\ImageGenerators;

use Spatie\MediaLibrary\ImageGenerator\FileTypes\Video;
use Spatie\MediaLibrary\Test\TestCase;

class VideoTest extends TestCase
{
    /** @test */
    public function it_can_convert_a_video()
    {
        $imageGenerator = new Video();

        if (! $imageGenerator->areRequirementsInstalled()) {
            $this->markTestSkipped('Skipping video test because requirements to run it are not met');
        }

        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWebm())->toMediaLibrary();

        $this->assertTrue($imageGenerator->canConvert($media->getPath()));

        $imageFile = $imageGenerator->convert($media->getPath());

        $this->assertEquals('image/jpeg', mime_content_type($imageFile));

        //$this->assertEquals($imageFile, $media->getPath());
    }
}