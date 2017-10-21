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
        $createdAt = filemtime($derivedImage);

        unlink($derivedImage);

        $this->assertFileNotExists($derivedImage);

        sleep(1);

        Artisan::call('medialibrary:regenerate');

        $this->assertFileExists($derivedImage);
        $this->assertGreaterThan($createdAt, filemtime($derivedImage));
    }

    /** @test */
    public function it_can_regenerate_only_missing_files()
    {
        $mediaExists = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');
        $mediaMissing = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.png'))->toMediaCollection('images');

        $derivedImageMissing = $this->getMediaDirectory("{$mediaMissing->id}/conversions/thumb.jpg");
        $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/thumb.jpg");

        $existsCreatedAt = filemtime($derivedImageExists);
        $missingCreatedAt = filemtime($derivedImageMissing);

        unlink($derivedImageMissing);

        $this->assertFileNotExists($derivedImageMissing);

        sleep(1);

        Artisan::call('medialibrary:regenerate', [
            '--only-missing' => true,
        ]);

        $this->assertFileExists($derivedImageMissing);

        $this->assertSame($existsCreatedAt, filemtime($derivedImageExists));
        $this->assertGreaterThan($missingCreatedAt, filemtime($derivedImageMissing));
    }

    /** @test */
    public function it_can_regenerate_all_files_of_named_conversions()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/thumb.jpg");
        $derivedMissingImage = $this->getMediaDirectory("{$media->id}/conversions/keep_original_format.jpg");

        unlink($derivedImage);
        unlink($derivedMissingImage);

        $this->assertFileNotExists($derivedImage);
        $this->assertFileNotExists($derivedMissingImage);

        Artisan::call('medialibrary:regenerate', [
            '--only' => 'thumb',
        ]);

        $this->assertFileExists($derivedImage);
        $this->assertFileNotExists($derivedMissingImage);
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
