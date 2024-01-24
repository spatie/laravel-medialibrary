<?php

use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGeneratedEvent;

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

it('triggers an event when the responsive images are generated', function () {
    Event::fake(ResponsiveImagesGeneratedEvent::class);

    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    Event::assertDispatched(ResponsiveImagesGeneratedEvent::class);
});

it('cleans the responsive images urls from the db before regeneration', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->withResponsiveImages()
        ->toMediaCollection();

    expect($media->fresh()->responsive_images['thumb']['urls'])->toHaveCount(1);

    $this->artisan('media-library:regenerate');
    expect($media->fresh()->responsive_images['thumb']['urls'])->toHaveCount(1);
});

it('will add responsive image entries when there were none when regenerating', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestFilesDirectory('test.jpg'))
        ->withResponsiveImages()
        ->toMediaCollection();

    // remove all responsive image db entries
    $responsiveImages = $media->responsive_images;
    $responsiveImages['thumb']['urls'] = [];
    $media->responsive_images = $responsiveImages;
    $media->save();
    expect($media->fresh()->responsive_images['thumb']['urls'])->toHaveCount(0);

    $this->artisan('media-library:regenerate');
    expect($media->fresh()->responsive_images['thumb']['urls'])->toHaveCount(1);
});

it('will generate tiny placeholders when tiny placeholders are turned on', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $responsiveImage = $media->refresh()->responsive_images;

    expect($responsiveImage['media_library_original'])->toHaveKey('base64svg');

    expect((string) $responsiveImage['media_library_original']['base64svg'])->toContain('data:image/svg+xml;base64,');
});

it('will not generate tiny placeholders when tiny placeholders are turned off', function () {
    config()->set('media-library.responsive_images.use_tiny_placeholders', false);

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $responsiveImage = $media->refresh()->responsive_images;

    expect($responsiveImage['media_library_original'])->not()->toHaveKey('base64svg');
});
