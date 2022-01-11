<?php

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGenerated;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('can generate responsive images', function () {
    $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"));
    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg"));
    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg"));
});

it('will generate responsive images if with responsive images if returns true', function () {
    $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImagesIf(fn () => true)
            ->toMediaCollection();

    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"));
    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg"));
    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg"));
});

it('will not generate responsive images if with responsive images if returns false', function () {
    $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImagesIf(fn () => false)
            ->toMediaCollection();

    $this->assertFileDoesNotExist($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"));
});

test('its conversions can have responsive images', function () {
    $this->testModelWithResponsiveImages
                ->addMedia($this->getTestJpg())
                ->withResponsiveImages()
                ->toMediaCollection();

    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg"));
});

test('its conversions can have responsive images and change format', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestPng())
        ->withResponsiveImages()
        ->toMediaCollection();

    $this->assertFileExists($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___pngtojpg_700_883.jpg"));
});

it('triggers an event when the responsive images are generated', function () {
    Event::fake(ResponsiveImagesGenerated::class);

    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    Event::assertDispatched(ResponsiveImagesGenerated::class);
});

it('cleans the responsive images urls from the db before regeneration', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestFilesDirectory("test.jpg"))
        ->withResponsiveImages()
        ->toMediaCollection();

    $this->assertCount(1, $media->fresh()->responsive_images["thumb"]["urls"]);

    $this->artisan("media-library:regenerate");
    $this->assertCount(1, $media->fresh()->responsive_images["thumb"]["urls"]);
});

it('will add responsive image entries when there were none when regenerating', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestFilesDirectory("test.jpg"))
        ->withResponsiveImages()
        ->toMediaCollection();

    // remove all responsive image db entries
    $responsiveImages = $media->responsive_images;
    $responsiveImages["thumb"]["urls"] = [];
    $media->responsive_images = $responsiveImages;
    $media->save();
    $this->assertCount(0, $media->fresh()->responsive_images["thumb"]["urls"]);

    $this->artisan("media-library:regenerate");
    $this->assertCount(1, $media->fresh()->responsive_images["thumb"]["urls"]);
});
