<?php

use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

test('a media instance can get responsive image urls', function () {
    $this
        ->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertEquals([
        "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg",
        "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg",
        "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg",
    ], $media->getResponsiveImageUrls());

    $this->assertEquals([
        "http://localhost/media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls("thumb"));

    $this->assertEquals([], $media->getResponsiveImageUrls("non-existing-conversion"));
});

test('a media instance can generate the contents of scrset', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertStringContainsString(
        "http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg 340w, http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_284_233.jpg 284w, http://localhost/media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg 237w",
        $media->getSrcset()
    );
    $this->assertStringContainsString("data:image/svg+xml;base64", $media->getSrcset());

    $this->assertStringContainsString(
        "http://localhost/media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg 50w",
        $media->getSrcset("thumb")
    );
    $this->assertStringContainsString("data:image/svg+xml;base64,", $media->getSrcset("thumb"));
});

test('a responsive image can return some properties', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModel->getFirstMedia();

    $responsiveImage = $media->responsiveImages()->files->first();

    $this->assertEquals("media_library_original", $responsiveImage->generatedFor());

    $this->assertEquals(340, $responsiveImage->width());

    $this->assertEquals(280, $responsiveImage->height());
});

test('responsive image generation respects the conversion quality setting', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection("default");

    $standardQualityResponsiveConversion = $this->getTempDirectory("media/1/responsive-images/{$this->fileName}___standardQuality_340_280.jpg");
    $lowerQualityResponsiveConversion = $this->getTempDirectory("media/1/responsive-images/{$this->fileName}___lowerQuality_340_280.jpg");

    $this->assertLessThan(filesize($standardQualityResponsiveConversion), filesize($lowerQualityResponsiveConversion));
});

test('a media instance can get responsive image urls with conversions stored on second media disk', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->storingConversionsOnDisk("secondMediaDisk")
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertEquals([
        "http://localhost/media2/1/responsive-images/{$this->fileName}___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls("thumb"));
});

it('can handle file names with underscore', function () {
    $this
        ->testModelWithResponsiveImages
        ->addMedia($this->getTestImageEndingWithUnderscore())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertSame([
        "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_340_280.jpg",
        "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_284_233.jpg",
        "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_237_195.jpg",
    ], $media->getResponsiveImageUrls());

    $this->assertSame([
        "http://localhost/media/1/responsive-images/{$this->fileNameWithUnderscore}___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls("thumb"));

    $this->assertSame([], $media->getResponsiveImageUrls("non-existing-conversion"));
});
