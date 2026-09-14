<?php

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Exceptions\VirtualConversionHasNoFile;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModelWithDriverConversions;

beforeEach(function () {
    Http::fake(['*' => Http::response('bytes', 200)]);

    config()->set('media-library.image_drivers.cloudflare.zone', 'https://example.com');
    config()->set('media-library.image_drivers.cloudflare-delivery.zone', 'https://example.com');

    $this->model = TestModelWithDriverConversions::create(['name' => 'test']);
    $this->media = $this->model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();
});

it('renders a virtual conversion as html without stored responsive images', function () {
    $html = $this->media->img('hero')->toHtml();

    expect($html)->toContain('srcset=')
        ->and($html)->toContain('https://example.com/cdn-cgi/image/');
});

it('falls back to the original when only virtual conversions are available', function () {
    expect($this->media->toAvailableResponse(request(), ['hero']))
        ->not->toBeNull();
});

it('does not hand out a temporary url for a virtual conversion', function () {
    expect(fn () => $this->media->getTemporaryUrl(now()->addHour(), 'hero'))
        ->toThrow(VirtualConversionHasNoFile::class);
});

it('skips virtual conversions when looking for an available temporary url', function () {
    expect(fn () => $this->media->getAvailableTemporaryUrl(['hero']))
        ->not->toThrow(VirtualConversionHasNoFile::class);
});

it('does not stream a virtual conversion', function () {
    expect(fn () => $this->media->stream('hero'))
        ->toThrow(VirtualConversionHasNoFile::class);
});

it('still resolves the edge url for the virtual conversion', function () {
    expect($this->media->getUrl('hero'))->toStartWith('https://example.com/cdn-cgi/image/');
});
