<?php

namespace Spatie\MediaLibrary\Test\Conversion;

use Spatie\MediaLibrary\Test\TestCase;
use Illuminate\Support\Facades\Artisan;

class RegenerateCommandTest extends TestCase
{
    /** @test */
    public function it_can_regenerate_all_files()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/thumb.jpg");

        unlink($derivedImage);

        $this->assertFileNotExists($derivedImage);

        Artisan::call('medialibrary:regenerate');

        $this->assertFileExists($derivedImage);
    }

    /** @test */
    function it_can_regenerate_only_missing_files()
    {
        $mediaExists = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');
        $mediaMissing = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.png'))->toMediaCollection('images');

        $existsCreatedAt = filemtime($this->getMediaDirectory("{$mediaExists->id}/conversions/thumb.jpg"));
        $missingCreatedAt = filemtime($this->getMediaDirectory("{$mediaMissing->id}/conversions/thumb.jpg"));

        $derivedImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/thumb.jpg");

        unlink($derivedImage);

        $this->assertFileNotExists($derivedImage);

        sleep(1);

        Artisan::call('medialibrary:regenerate', [
            '--only-missing' => true
        ]);

        $this->assertFileExists($derivedImage);

        $this->assertSame(
            $existsCreatedAt,
            filemtime($this->getMediaDirectory("{$mediaExists->id}/conversions/thumb.jpg"))
        );

        $this->assertGreaterThan(
            $missingCreatedAt,
            filemtime($this->getMediaDirectory("{$mediaMissing->id}/conversions/thumb.jpg"))
        );
    }

    /** @test */
    public function it_can_regenerate_files_by_media_ids()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('images');

        $media2 = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

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
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');

        unlink($this->getMediaDirectory($media->id.'/test.jpg'));

        $result = Artisan::call('medialibrary:regenerate');

        $this->assertEquals(0, $result);
    }
}
