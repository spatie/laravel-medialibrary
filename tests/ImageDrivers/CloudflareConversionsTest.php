<?php

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Exceptions\VirtualConversionHasNoFile;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithDriverConversions;

beforeEach(function () {
    config()->set('media-library.image_drivers.cloudflare.zone', 'https://example.com');
    config()->set('media-library.image_drivers.cloudflare-delivery.zone', 'https://example.com');

    $this->model = TestModelWithDriverConversions::create(['name' => 'test']);
});

it('builds a delivery url for a virtual conversion instead of generating a file', function () {
    Http::fake([
        '*' => Http::response('transformed-bytes', 200),
    ]);

    $media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $heroUrl = $media->getUrl('hero');

    expect($heroUrl)->toStartWith('https://example.com/cdn-cgi/image/')
        ->and($heroUrl)->toContain('width=1600')
        ->and($heroUrl)->toContain('quality=75')
        ->and($heroUrl)->toContain('format=auto')
        ->and($heroUrl)->toEndWith($media->getFullUrl())
        ->and($media->hasGeneratedConversion('hero'))->toBeTrue()
        // It is virtual: nothing was generated, so it is absent from generated_conversions.
        ->and($media->generated_conversions)->not->toHaveKey('hero');
});

it('does not store a file for a virtual conversion and throws on getPath', function () {
    Http::fake(['*' => Http::response('transformed-bytes', 200)]);

    $media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    expect(fn () => $media->getPath('hero'))->toThrow(VirtualConversionHasNoFile::class);
});

it('fetches and stores the file for a mode A cloudflare conversion', function () {
    Http::fake([
        'example.com/cdn-cgi/image/*' => Http::response('transformed-bytes', 200),
    ]);

    $media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    expect($media->hasGeneratedConversion('avatar'))->toBeTrue()
        ->and(file_exists($media->getPath('avatar')))->toBeTrue()
        ->and($media->getPath('avatar'))->toEndWith('.webp')
        ->and(file_get_contents($media->getPath('avatar')))->toBe('transformed-bytes');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'gravity=face')
        && str_contains($request->url(), 'fit=cover'));
});

it('runs the local spatie closure conversion normally', function () {
    Http::fake(['*' => Http::response('transformed-bytes', 200)]);

    $media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();

    [$width, $height] = getimagesize($media->getPath('thumb'));

    expect($width)->toBe(40)->and($height)->toBe(40);
});
