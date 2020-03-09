<?php

namespace Spatie\MediaLibrary\Tests\Conversions;

use Spatie\MediaLibrary\Tests\TestCase;

class ConversionFileExtensionTest extends TestCase
{
    /** @test */
    public function it_defaults_to_jpg_when_the_original_file_is_an_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

        $this->assertExtensionEquals('jpg', $media->getUrl('thumb'));
    }

    /** @test */
    public function it_can_keep_the_original_image_format_if_the_original_file_is_an_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestPng())->toMediaCollection();

        $this->assertExtensionEquals('png', $media->getUrl('keep_original_format'));
    }

    /** @test */
    public function it_always_defaults_to_jpg_when_the_original_file_is_not_an_image()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestMp4())->toMediaCollection();

        $this->assertExtensionEquals('jpg', $media->getUrl('thumb'));
        $this->assertExtensionEquals('jpg', $media->getUrl('keep_original_format'));
    }

    private function assertExtensionEquals(string $expectedExtension, string $file)
    {
        $actualExtension = pathinfo($file, PATHINFO_EXTENSION);

        $this->assertEquals($expectedExtension, $actualExtension);
    }
}
