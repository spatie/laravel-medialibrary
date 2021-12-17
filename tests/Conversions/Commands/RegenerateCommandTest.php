<?php

namespace Spatie\MediaLibrary\Tests\Conversions\Commands;

use Spatie\MediaLibrary\Tests\TestCase;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithConversion;

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

    /** @test */
    public function it_can_regenerate_responsive_images()
    {
        $media = $this
            ->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->withResponsiveImages()
            ->toMediaCollection();

        $responsiveImages = glob($this->getMediaDirectory($media->id.'/responsive-images/*'));

        array_map('unlink', $responsiveImages);

        $this->artisan('media-library:regenerate', ['--with-responsive-images' => true])->assertExitCode(0);

        foreach ($responsiveImages as $image) {
            $this->assertFileExists($image);
        }
    }

    /** @test */
    public function it_can_regenerate_files_by_starting_from_id()
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

        $this->artisan('media-library:regenerate', ['--starting-from-id' => $media2->getKey()]);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileExists($derivedImage2);
    }

    /** @test */
    public function it_can_regenerate_files_starting_after_the_provided_id()
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

        $this->artisan('media-library:regenerate', [
            '--starting-from-id' => $media->getKey(),
            '--exclude-starting-id' => true,
        ]);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileExists($derivedImage2);
    }

    /** @test */
    public function it_can_regenerate_files_starting_after_the_provided_id_with_shortcut()
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

        $this->artisan('media-library:regenerate', [
            '--starting-from-id' => $media->getKey(),
            '-X' => true,
        ]);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileExists($derivedImage2);
    }

    /** @test */
    public function it_can_regenerate_files_starting_from_id_with_model_type()
    {
        $media = $this->testModelWithConversionsOnOtherDisk
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('images');

        $media2 = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('images');

        $media3 = $this->testModelWithConversion
            ->addMedia($this->getTestFilesDirectory('test.jpg'))
            ->preservingOriginal()
            ->toMediaCollection('images');

        $derivedImage = $this->getMediaDirectory("{$media->id}/conversions/test-thumb.jpg");
        $derivedImage2 = $this->getMediaDirectory("{$media2->id}/conversions/test-thumb.jpg");
        $derivedImage3 = $this->getMediaDirectory("{$media3->id}/conversions/test-thumb.jpg");

        unlink($derivedImage);
        unlink($derivedImage2);
        unlink($derivedImage3);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileDoesNotExist($derivedImage2);
        $this->assertFileDoesNotExist($derivedImage3);

        $this->artisan('media-library:regenerate', [
            '--starting-from-id' => $media->getKey(),
            'modelType' => TestModelWithConversion::class,
        ]);

        $this->assertFileDoesNotExist($derivedImage);
        $this->assertFileExists($derivedImage2);
        $this->assertFileExists($derivedImage3);
    }
}
