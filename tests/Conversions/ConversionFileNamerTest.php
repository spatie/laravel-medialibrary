<?php

namespace Spatie\MediaLibrary\Tests\Conversions;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\testfiles\TestConversionFileNamer;

class ConversionFileNamerTest extends TestCase
{
    /** @test */
    public function it_can_use_a_custom_conversion_file_namer()
    {
        config()->set('media-library.conversion_file_namer', TestConversionFileNamer::class);

        $this
            ->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $path = $this->testModelWithConversion->refresh()->getFirstMediaPath('default', 'thumb');

        $this->assertStringEndsWith('test---thumb.jpg', $path);
        $this->assertFileExists($path);

        $this->assertEquals('/media/1/conversions/test---thumb.jpg', $this->testModelWithConversion->getFirstMediaUrl('default', 'thumb'));
    }
}
