<?php

use Illuminate\Support\Facades\Event;
use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGeneratedEvent;
use Programic\MediaLibrary\Support\FileRemover\DefaultFileRemover;
use Programic\MediaLibrary\Tests\TestSupport\TestCustomPathGenerator;

beforeEach(function () {
    $this->fileName = 'test';
    $this->fileNameWithUnderscore = 'test_';
    $this->conversionName = 'test';
    $this->conversion = new Conversion($this->conversionName);
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

it('will not delete responsive images of images with similar names saved on the same directory', function () {

    config(['media-library.path_generator' => TestCustomPathGenerator::class]);
    config(['media-library.file_remover_class' => DefaultFileRemover::class]);

    $media = $this->testModelWithMultipleConversions->addMedia($this->getTestJpg())->withResponsiveImages()->toMediaCollection();

    $media2 = $this->testModelWithMultipleConversions->addMedia($this->getTestImageEndingWithUnderscore())->withResponsiveImages()->toMediaCollection();

    expect($this->getTempDirectory("media/some_user/1/{$this->fileName}.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileName}___media_library_original_237_195.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileName}___media_library_original_284_234.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileName}___media_library_original_340_280.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileName}-small.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileName}-medium.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileName}-large.jpg"))->toBeFile();

    expect($this->getTempDirectory("media/some_user/1/{$this->fileNameWithUnderscore}.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_237_195.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_284_234.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_340_280.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-small.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-medium.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-large.jpg"))->toBeFile();

    $media->delete();

    expect(File::exists($this->getTempDirectory("media/some_user/1/{$this->fileName}.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileName}___media_library_original_237_195.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileName}___media_library_original_284_234.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileName}___media_library_original_340_280.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileName}-small")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileName}-medium")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileName}-large")))->toBeFalse();

    // checks if the other media files are still there
    expect($this->getTempDirectory("media/some_user/1/{$this->fileNameWithUnderscore}.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_237_195.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_284_234.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_340_280.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-small.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-medium.jpg"))->toBeFile();
    expect($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-large.jpg"))->toBeFile();

    $media2->delete();

    expect(File::exists($this->getTempDirectory("media/some_user/1/{$this->fileNameWithUnderscore}.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_237_195.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_284_234.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_responsive_images/{$this->fileNameWithUnderscore}___media_library_original_340_280.jpg")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-small")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-medium")))->toBeFalse();
    expect(File::exists($this->getTempDirectory("media/some_user/1/custom_conversions/{$this->fileNameWithUnderscore}-large")))->toBeFalse();
});
