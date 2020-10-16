<?php

namespace Spatie\MediaLibrary\Tests\Conversions;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;

class ConversionFileNamerTest extends TestCase
{
    public string $fileName = "prefix_test_suffix";

    /** @test */
    public function it_can_use_a_custom_file_namer()
    {
        config()->set("media-library.file_namer", TestFileNamer::class);

        $this
            ->testModelWithConversion
            ->addMedia($this->getTestJpg())
            ->toMediaCollection();

        $path = $this->testModelWithConversion->refresh()->getFirstMediaPath("default", "thumb");

        $this->assertStringEndsWith("{$this->fileName}---thumb.jpg", $path);
        $this->assertFileExists($path);

        $this->assertEquals("/media/1/conversions/{$this->fileName}---thumb.jpg", $this->testModelWithConversion->getFirstMediaUrl("default", "thumb"));
    }
}
