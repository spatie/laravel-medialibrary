<?php

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Exceptions\ResponsiveImagesNotSupported;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithCloudflareModeAResponsive;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithDriverConversions;

beforeEach(function () {
    Http::fake(['*' => Http::response('bytes', 200)]);
    config()->set('media-library.image_drivers.cloudflare.zone', 'https://example.com');

    config()->set('media-library.image_drivers.cloudflare-delivery.zone', 'https://example.com');
    config()->set('media-library.image_drivers.cloudflare-delivery.responsive_widths', [320, 640, 1280]);

    $this->model = TestModelWithDriverConversions::create(['name' => 'test']);
    $this->media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
});

it('builds a responsive srcset of edge urls for a virtual conversion', function () {
    $srcset = $this->media->getSrcset('hero');

    $original = $this->media->getFullUrl();

    expect($srcset)->toContain("https://example.com/cdn-cgi/image/format=auto,quality=75,width=320/{$original} 320w")
        ->and($srcset)->toContain("width=640/{$original} 640w")
        ->and($srcset)->toContain("width=1280/{$original} 1280w");
});

it('exposes the responsive urls and reports having them', function () {
    expect($this->media->getResponsiveImageUrls('hero'))->toHaveCount(3)
        ->and($this->media->hasResponsiveImages('hero'))->toBeTrue();
});

it('overrides the closure width with each responsive width', function () {
    $srcset = $this->media->getSrcset('hero');

    expect($srcset)->not->toContain('width=1600');
});

it('does not build a virtual srcset when the conversion has no responsive images', function () {
    expect($this->media->getSrcset('avatar'))->toBe('')
        ->and($this->media->hasResponsiveImages('avatar'))->toBeFalse();
});

it('throws when responsive images are requested on the file storing cloudflare driver', function () {
    Http::fake(['*' => Http::response('bytes', 200)]);
    config()->set('media-library.image_drivers.cloudflare.zone', 'https://example.com');

    $model = TestModelWithCloudflareModeAResponsive::create(['name' => 'test']);

    expect(fn () => $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection())
        ->toThrow(ResponsiveImagesNotSupported::class);
});
