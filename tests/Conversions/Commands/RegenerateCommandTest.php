<?php

namespace Spatie\MediaLibrary\Tests\Conversions\Commands;

use Spatie\MediaLibrary\Tests\TestCase;

class RegenerateCommandTest extends TestCase
{
    /** @test */
    public function it_can_regenerate_all_files()
    {
        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
        $createdAt = filemtime($derivedImage);

        unlink($derivedImage);

        $this->assertFileDoesNotExist($derivedImage);

        sleep(1);

        $this->artisan('media-library:regenerate');

        $this->assertFileExists($derivedImage);
        $this->assertGreaterThan($createdAt, filemtime($derivedImage));
    }

    /** @test */
    public function it_can_regenerate_only_missing_files()
    {
        $mediaExists = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

        $mediaMissing = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.png'))
            ->toMediaCollection('images');

        $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/test-thumb.jpg");

        $derivedMissingImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-thumb.jpg");

        $existsCreatedAt = filemtime($derivedImageExists);

        $missingCreatedAt = filemtime($derivedMissingImage);

        unlink($derivedMissingImage);

        $this->assertFileDoesNotExist($derivedMissingImage);

        sleep(1);

        $this->artisan('media-library:regenerate', [
            '--only-missing' => true,
        ]);

        $this->assertFileExists($derivedMissingImage);

        $this->assertSame($existsCreatedAt, filemtime($derivedImageExists));

        $this->assertGreaterThan($missingCreatedAt, filemtime($derivedMissingImage));
    }

    /** @test */
    public function it_can_regenerate_missing_files_queued()
    {
        $mediaExists = $this
            ->testModelWithConversionQueued
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

        $mediaMissing = $this
            ->testModelWithConversionQueued
            ->addMedia($this->getTestFilesDirectory('test.png'))
            ->toMediaCollection('images');

        $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/test-thumb.jpg");

        $derivedMissingImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-thumb.jpg");

        $existsCreatedAt = filemtime($derivedImageExists);

        $missingCreatedAt = filemtime($derivedMissingImage);

        unlink($derivedMissingImage);

        $this->assertFileDoesNotExist($derivedMissingImage);

        sleep(1);

        $this->artisan('media-library:regenerate', [
            '--only-missing' => true,
        ]);

        $this->assertFileExists($derivedMissingImage);

        $this->assertSame($existsCreatedAt, filemtime($derivedImageExists));

        $this->assertGreaterThan($missingCreatedAt, filemtime($derivedMissingImage));
    }

    /** @test */
    public function it_can_regenerate_all_files_of_named_conversions()
    {
        $media = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
        $derivedMissingImage = $this->getMediaDirectory("{$media->id}/conversions/test-keep_original_format.jpg");

        unlink($derivedImage);
        unlink($derivedMissingImage);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileDoesNotExist($derivedMissingImage);

        $this->artisan('media-library:regenerate', [
            '--only' => 'thumb',
        ]);

        $this->assertFileExists($derivedImage);
        $this->assertFileDoesNotExist($derivedMissingImage);
    }

    /** @test */
    public function it_can_regenerate_only_missing_files_of_named_conversions()
    {
        $mediaExists = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

        $mediaMissing = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.png'))
            ->toMediaCollection('images');

        $derivedImageExists = $this->getMediaDirectory("{$mediaExists->id}/conversions/test-thumb.jpg");
        $derivedMissingImage = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-thumb.jpg");
        $derivedMissingImageOriginal = $this->getMediaDirectory("{$mediaMissing->id}/conversions/test-keep_original_format.png");

        $existsCreatedAt = filemtime($derivedImageExists);
        $missingCreatedAt = filemtime($derivedMissingImage);

        unlink($derivedMissingImage);
        unlink($derivedMissingImageOriginal);

        $this->assertFileDoesNotExist($derivedMissingImage);
        $this->assertFileDoesNotExist($derivedMissingImageOriginal);

        sleep(1);

        $this->artisan('media-library:regenerate', [
            '--only-missing' => true,
            '--only' => 'thumb',
        ]);

        $this->assertFileExists($derivedMissingImage);
        $this->assertFileDoesNotExist($derivedMissingImageOriginal);
        $this->assertSame($existsCreatedAt, filemtime($derivedImageExists));
        $this->assertGreaterThan($missingCreatedAt, filemtime($derivedMissingImage));
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

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
        $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

        unlink($derivedImage);
        unlink($derivedImage2);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileDoesNotExist($derivedImage2);

        $this->artisan('media-library:regenerate', ['--ids' => [2]]);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileExists($derivedImage2);
    }

    /** @test */
    public function it_can_regenerate_files_by_comma_separated_media_ids()
    {
        $media = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('images');

        $media2 = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
        $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");

        unlink($derivedImage);
        unlink($derivedImage2);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileDoesNotExist($derivedImage2);

        $this->artisan('media-library:regenerate', ['--ids' => ['1,2']]);

        $this->assertFileExists($derivedImage);
        $this->assertFileExists($derivedImage2);
    }

    /** @test */
    public function it_can_regenerate_files_even_if_there_are_files_missing()
    {
        $media = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->toMediaCollection('images');

        unlink($this->getMediaDirectory($media->id.'/test.jpg'));

        $this->artisan('media-library:regenerate')->assertExitCode(0);
    }
}
