<?php

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestFileNamer;

uses(TestCase::class);

it('can use a custom file namer', function () {
    config()->set("media-library.file_namer", TestFileNamer::class);

    $this
        ->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $path = $this->testModelWithConversion->refresh()->getFirstMediaPath("default", "thumb");

    $this->assertStringEndsWith("{$this->fileName}---thumb.jpg", $path);
    $this->assertFileExists($path);

    $this->assertEquals("/media/1/conversions/{$this->fileName}---thumb.jpg", $this->testModelWithConversion->getFirstMediaUrl("default", "thumb"));
});
