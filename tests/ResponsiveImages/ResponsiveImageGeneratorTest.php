<?php

use Illuminate\Support\Facades\Event;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGeneratedEvent;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithoutMediaConversions;

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

it('can generate responsive animated images', function (string $driver) {
    config()->set('media-library.image_driver', $driver);
    config()->set('media-library.responsive_images.use_tiny_placeholders', false);
    config()->set('media-library.convert_gif_to_webp_using_gif2webp', true);

    $testModel = new class() extends TestModelWithoutMediaConversions
    {
        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('images')
                ->registerMediaConversions(function (Media $media) {
                    $this
                        ->addMediaConversion('webp')
                        ->withResponsiveImages()
                        ->greyscale()
                        ->format('webp');
                });
        }
    };

    $model = $testModel::create(['name' => 'testmodel']);
    $model->addMedia($this->getTestGif())
        ->toMediaCollection('images');

    $imagick = Image::useImageDriver(config('media-library.image_driver'))->loadFile($this->getTempDirectory("media/1/{$this->fileName}.gif"))->image();

    expect(count($imagick))->toBe(10);

    $imagick = Image::useImageDriver(config('media-library.image_driver'))->loadFile($this->getTempDirectory("media/1/responsive-images/{$this->fileName}___webp_267_267.webp"))->image();

    expect(count($imagick))->toBe(10);
})->with(['imagick']);
