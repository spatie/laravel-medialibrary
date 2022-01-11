<?php

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGenerated;

beforeEach(function () {
    $this->fileName = 'test';
});

it('can generate responsive images', function () {
    $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImages()
            ->toMediaCollection();

    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_284_234.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg"))->toBeFile();
});

it('will generate responsive images if with responsive images if returns true', function () {
    $this->testModel
            ->addMedia($this->getTestJpg())
            ->withResponsiveImagesIf(fn () => true)
            ->toMediaCollection();

    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_284_234.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg"))->toBeFile();
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

    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg"))->toBeFile();
});

test('its conversions can have responsive images and change format', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestPng())
        ->withResponsiveImages()
        ->toMediaCollection();

    expect($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___pngtojpg_700_883.jpg"))->toBeFile();
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

    expect($media->fresh()->responsive_images["thumb"]["urls"])->toHaveCount(1);

    $this->artisan("media-library:regenerate");
    expect($media->fresh()->responsive_images["thumb"]["urls"])->toHaveCount(1);
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
    expect($media->fresh()->responsive_images["thumb"]["urls"])->toHaveCount(0);

    $this->artisan("media-library:regenerate");
    expect($media->fresh()->responsive_images["thumb"]["urls"])->toHaveCount(1);
});
