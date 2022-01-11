<?php

use Spatie\MediaLibrary\ResponsiveImages\RegisteredResponsiveImages;
use Spatie\MediaLibrary\Tests\TestCase;

uses(TestCase::class);

it('will register generated responsive images in the db', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModel->getFirstMedia();

    $this->assertEquals([
        'test___media_library_original_340_280.jpg',
        'test___media_library_original_284_233.jpg',
        'test___media_library_original_237_195.jpg',
    ], $media->responsive_images['media_library_original']['urls']);
});

it('can render a srcset when the base64svg is not rendered yet', function () {
    $this->testModel
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $media = $this->testModel->getFirstMedia();

    $responsiveImages = $media->responsive_images;

    unset($responsiveImages['media_library_original']['base64svg']);

    $media->responsive_images = $responsiveImages;

    $registeredResponsiveImage = new RegisteredResponsiveImages($media);

    $this->assertNull($registeredResponsiveImage->getPlaceholderSvg());

    $this->assertNotEmpty($registeredResponsiveImage->getSrcset());
});
