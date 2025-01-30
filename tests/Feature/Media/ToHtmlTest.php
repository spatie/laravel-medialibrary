<?php

use Illuminate\Support\Str;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithCustomLoadingAttribute;
use Programic\Snapshots\MatchesSnapshots;

uses(MatchesSnapshots::class);

beforeEach(function () {
    $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->preservingOriginal()
        ->toMediaCollection();
});

it('can render itself as an image', function () {
    $this->assertEquals(
        '<img src="/media/1/test.jpg" alt="test">',
        Media::first()->img(),
    );
});

it('can render a conversion of itself as an image', function () {
    $this->assertEquals(
        '<img src="/media/1/conversions/test-thumb.jpg" alt="test">',
        Media::first()->img('thumb')
    );
});

it('can render extra attributes', function () {
    $this->assertEquals(
        '<img class="my-class" id="my-id" src="/media/1/conversions/test-thumb.jpg" alt="test">',
        Media::first()->img('thumb', ['class' => 'my-class', 'id' => 'my-id']),
    );
});

it('can render an array of classes as extra attributes', function () {
    $this->assertEquals(
        '<img class="rounded border" src="/media/1/conversions/test-thumb.jpg" alt="test">',
        Media::first()->img('thumb', ['class' => [
            'rounded',
            'border' => true,
            'border-black' => false,
        ]]),
    );
});

it('can render an array of styles as extra attributes', function () {
    $this->assertEquals(
        '<img style="background-color: blue; color: blue;" src="/media/1/conversions/test-thumb.jpg" alt="test">',
        Media::first()->img('thumb', ['style' => [
            'background-color: blue',
            'color: blue' => true,
            'width: 10px' => false,
        ]]),
    );
});

test('a media instance is htmlable', function () {
    $media = Media::first();

    $renderedView = $this->renderView('media', compact('media'));

    $this->assertEquals(
        '<img src="/media/1/test.jpg" alt="test"> <img src="/media/1/conversions/test-thumb.jpg" alt="test">',
        $renderedView,
    );
});

test('converting a non image to an image tag will not blow up', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestTiff())
        ->toMediaCollection();

    expect($media->img())->toEqual('');
});

it('can render pdf thumbnail as an image', function () {
    config()->set('media-library.image_driver', 'imagick');

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestPdf())
        ->toMediaCollection();

    $this->assertEquals(
        "<img src=\"/media/{$media->id}/conversions/test-thumb.jpg\" alt=\"test\">",
        $media->img('thumb'),
    );
});

it('can render itself with responsive images and a placeholder', function () {
    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $image = $media->refresh()->img();

    expect(substr_count($image, '/media/2/responsive-images/'))->toEqual(3);
    expect(Str::contains($image, 'data:image/svg+xml;base64,'))->toBeTrue();
});

it('can render itself with responsive images of a conversion and a placeholder', function () {
    $media = $this->testModelWithResponsiveImages
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $image = $media->refresh()->img('thumb');

    expect((string) $image)->toContain('/media/2/responsive-images/');
    expect((string) $image)->toContain('data:image/svg+xml;base64,');
});

it('will not render extra javascript or include base64 svg when tiny placeholders are turned off', function () {
    config()->set('media-library.responsive_images.use_tiny_placeholders', false);

    $media = $this->testModelWithConversion
        ->addMedia($this->getTestJpg())
        ->withResponsiveImages()
        ->toMediaCollection();

    $imgTag = $media->refresh()->img();

    expect($imgTag)->toEqual('<img srcset="/media/2/responsive-images/test___media_library_original_340_280.jpg 340w, /media/2/responsive-images/test___media_library_original_284_234.jpg 284w, /media/2/responsive-images/test___media_library_original_237_195.jpg 237w" src="/media/2/test.jpg" width="340" height="280" alt="test">');
});

test('the loading attribute can be specified on the conversion', function () {
    $media = TestModelWithCustomLoadingAttribute::create(['name' => 'test'])
        ->addMedia($this->getTestJpg())
        ->toMediaCollection();

    $originalImgTag = $media->refresh()->img();
    expect($originalImgTag)->toEqual('<img src="/media/2/test.jpg" alt="test">');

    $lazyConversionImageTag = $media->refresh()->img('lazy-conversion');
    expect($lazyConversionImageTag)->toEqual('<img loading="lazy" src="/media/2/conversions/test-lazy-conversion.jpg" alt="test">');

    $eagerConversionImageTag = $media->refresh()->img('eager-conversion');
    expect($eagerConversionImageTag)->toEqual('<img loading="eager" src="/media/2/conversions/test-eager-conversion.jpg" alt="test">');
});

it('has a shorthand function to use lazy loading', function () {
    $this->assertEquals(
        '<img loading="lazy" src="/media/1/test.jpg" alt="test">',
        Media::first()->img()->lazy()
    );
});

it('can set extra attributes', function () {
    $this->assertEquals(
        '<img extra="value" src="/media/1/test.jpg" alt="test">',
        (string) Media::first()->img()->attributes(['extra' => 'value'])
    );
});
