<?php

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareDeliveryImageDriver;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImageDriver;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * These tests hit the real Cloudflare edge. They run only when
 * CLOUDFLARE_IMAGES_ZONE and CLOUDFLARE_TEST_IMAGE_URL are set, and are skipped
 * everywhere else, so CI and local runs never need credentials.
 *
 * CLOUDFLARE_IMAGES_ZONE is a zone with image transformations enabled, for
 * example https://your-site.com. CLOUDFLARE_TEST_IMAGE_URL is a publicly
 * reachable image hosted on an origin that zone is allowed to fetch from.
 */
beforeEach(function () {
    $zone = env('CLOUDFLARE_IMAGES_ZONE');
    $sourceUrl = env('CLOUDFLARE_TEST_IMAGE_URL');

    if (! $zone || ! $sourceUrl) {
        $this->markTestSkipped('Set CLOUDFLARE_IMAGES_ZONE and CLOUDFLARE_TEST_IMAGE_URL to run the live Cloudflare tests.');
    }

    $this->config = ['zone' => $zone];

    // A media whose full url points at the public test image, so Cloudflare has
    // a reachable original to transform without needing a public media disk.
    $media = new class extends Media
    {
        public string $liveSourceUrl = '';

        public function getFullUrl(string $conversionName = ''): string
        {
            return $this->liveSourceUrl;
        }
    };

    $media->liveSourceUrl = $sourceUrl;
    $media->file_name = 'source.jpg';
    $media->mime_type = 'image/jpeg';

    $this->media = $media;
});

it('transforms and downloads a real image through cloudflare', function () {
    $conversion = Conversion::create('cf')
        ->manipulate(fn (CloudflareImage $image) => $image
            ->width(50)
            ->height(50)
            ->fit(CloudflareFit::Cover)
            ->format(CloudflareFormat::Webp)
        );

    $file = tempnam(sys_get_temp_dir(), 'cf').'.webp';

    (new CloudflareImageDriver($this->config))->convert($this->media, $conversion, $file);

    expect(filesize($file))->toBeGreaterThan(0);

    [$width, $height] = getimagesize($file);

    expect($width)->toBeLessThanOrEqual(50)->and($height)->toBeLessThanOrEqual(50);

    @unlink($file);
});

it('serves a working delivery url from the edge', function () {
    $conversion = Conversion::create('cf')
        ->manipulate(fn (CloudflareImage $image) => $image->width(60)->format(CloudflareFormat::Auto));

    $url = (new CloudflareDeliveryImageDriver($this->config))->conversionUrl($this->media, $conversion);

    $response = Http::get($url);

    expect($response->status())->toBe(200)
        ->and($response->header('content-type'))->toContain('image');
});

it('serves each responsive srcset width from the edge', function () {
    $conversion = Conversion::create('cf')
        ->withResponsiveImages()
        ->manipulate(fn (CloudflareImage $image) => $image->format(CloudflareFormat::Auto));

    $driver = new CloudflareDeliveryImageDriver($this->config + ['responsive_widths' => [40, 80]]);

    $urls = $driver->responsiveConversionUrls($this->media, $conversion);

    $file = tempnam(sys_get_temp_dir(), 'cf');
    file_put_contents($file, Http::get($urls[40])->body());

    [$width] = getimagesize($file);

    expect($width)->toBeLessThanOrEqual(40);

    @unlink($file);
});
