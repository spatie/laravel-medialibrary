<?php

namespace Spatie\MediaLibrary\Tests\Conversions\ImageGenerators;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Audio;
use Spatie\MediaLibrary\Tests\TestCase;

class AudioTest extends TestCase
{
    /** @test */
    public function it_can_convert_audio()
    {
        $imageGenerator = new Audio();

        if (! $imageGenerator->requirementsAreInstalled()) {
            self::markTestSkipped('Skipping audio waveform test because requirements to run it are not met');
        }

        //Test mp3 format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestMp3())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.mp3', '.png', $media->getPath()));

        //Test aiff format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestAiff())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.aiff', '.png', $media->getPath()));

        //Test ogg format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestOgg())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.ogg', '.png', $media->getPath()));

        //Test wav format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWav())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.wav', '.png', $media->getPath()));

        //Test wma format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestWma())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.wma', '.png', $media->getPath()));

        //Test flac format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestFlac())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.flac', '.png', $media->getPath()));

        //Test m4a format
        $media = $this->testModelWithoutMediaConversions->addMedia($this->getTestM4a())->toMediaCollection();
        self::assertTrue($imageGenerator->canConvert($media));
        $imageFile = $imageGenerator->convert($media->getPath(), new Conversion('test'));
        self::assertEquals('image/png', mime_content_type($imageFile));
        self::assertEquals($imageFile, str_replace('.m4a', '.png', $media->getPath()));
    }
}
