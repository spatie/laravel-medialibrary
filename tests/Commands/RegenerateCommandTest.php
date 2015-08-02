<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Test\TestCase;

class RegenerateCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_regenerate_all_files()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/thumb.jpg");

        unlink($derivedImage);

        $this->assertFileNotExists($derivedImage);

        Artisan::call('medialibrary:regenerate');

        $this->assertFileExists($derivedImage);
    }
}
