<?php

beforeEach(function () {
    $this->fileName = 'test';
    $this->fileNameWithUnderscore = 'test_';
});

test('a media instance can get responsive image urls', function () {
    $this
        ->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertEquals([
        "/media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg",
        "/media/1/responsive-images/{$this->fileName}___media_library_original_284_234.jpg",
        "/media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg",
    ], $media->getResponsiveImageUrls());

    $this->assertEquals([
        "/media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls('thumb'));

    expect($media->getResponsiveImageUrls('non-existing-conversion'))->toEqual([]);
});

test('a media instance can generate the contents of scrset', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertStringContainsString(
        "/media/1/responsive-images/{$this->fileName}___media_library_original_340_280.jpg 340w, /media/1/responsive-images/{$this->fileName}___media_library_original_284_234.jpg 284w, /media/1/responsive-images/{$this->fileName}___media_library_original_237_195.jpg 237w",
        $media->getSrcset()
    );
    expect($media->getSrcset())->toContain('data:image/svg+xml;base64');

    $this->assertStringContainsString(
        "/media/1/responsive-images/{$this->fileName}___thumb_50_41.jpg 50w",
        $media->getSrcset('thumb')
    );
    expect($media->getSrcset('thumb'))->toContain('data:image/svg+xml;base64,');
});

test('a responsive image can return some properties', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModel->getFirstMedia();

    $responsiveImage = $media->responsiveImages()->files->first();

    expect($responsiveImage->generatedFor())->toEqual('media_library_original');

    expect($responsiveImage->width())->toEqual(340);

    expect($responsiveImage->height())->toEqual(280);
});

test('responsive image generation respects the conversion quality setting', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection('default');

    $standardQualityResponsiveConversion = $this->getTempDirectory("media/1/responsive-images/{$this->fileName}___standardQuality_340_280.jpg");
    $lowerQualityResponsiveConversion = $this->getTempDirectory("media/1/responsive-images/{$this->fileName}___lowerQuality_340_280.jpg");

    expect(filesize($lowerQualityResponsiveConversion))->toBeLessThan(filesize($standardQualityResponsiveConversion));
});

test('a media instance can get responsive image urls with conversions stored on second media disk', function () {
    $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->storingConversionsOnDisk('secondMediaDisk')
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertEquals([
        "/media2/1/responsive-images/{$this->fileName}___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls('thumb'));
});

it('can handle file names with underscore', function () {
    $this
        ->testModelWithResponsiveImages
        ->addMedia($this->getTestImageEndingWithUnderscore())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    $this->assertSame([
        "/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_340_280.jpg",
        "/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_284_234.jpg",
        "/media/1/responsive-images/{$this->fileNameWithUnderscore}___media_library_original_237_195.jpg",
    ], $media->getResponsiveImageUrls());

    $this->assertSame([
        "/media/1/responsive-images/{$this->fileNameWithUnderscore}___thumb_50_41.jpg",
    ], $media->getResponsiveImageUrls('thumb'));

    expect($media->getResponsiveImageUrls('non-existing-conversion'))->toBe([]);
});

test('a media instance can be set to not generate responsive urls', function () {
    $this
        ->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->withResponsiveImages(false)
        ->toMediaCollection();

    $media = $this->testModelWithResponsiveImages->getFirstMedia();

    expect($media->hasResponsiveImages())->toBeFalse();
    
    expect($media->hasResponsiveImages('thumb'))->toBeFalse();
});
