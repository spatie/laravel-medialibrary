<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Test\TestCase;

class RegenerateCommandTest extends TestCase
{
    /** @test */
    public function it_can_regenerate_all_files()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/thumb.jpg");

        unlink($derivedImage);

        $this->assertFileNotExists($derivedImage);

        Artisan::call('medialibrary:regenerate');

        $this->assertFileExists($derivedImage);
    }

    /** @test */
    public function it_can_regenerate_files_by_media_ids()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->preservingOriginal()
            ->toCollection('images');

        $media2 = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/thumb.jpg");
        $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/thumb.jpg");

        unlink($derivedImage);
        unlink($derivedImage2);

        $this->assertFileNotExists($derivedImage);
        $this->assertFileNotExists($derivedImage2);

        Artisan::call('medialibrary:regenerate', ['--ids' => [2]]);

        $this->assertFileNotExists($derivedImage);
        $this->assertFileExists($derivedImage2);
    }

    /** @test */
    public function it_can_regenerate_files_even_if_there_are_files_missing()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toCollection('images');

        unlink($this->getMediaDirectory($media->id . '/test.jpg'));

        Artisan::call('medialibrary:regenerate');
    }
}
