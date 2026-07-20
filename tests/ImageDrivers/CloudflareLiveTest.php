<?php

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;
use Spatie\MediaLibrary\Tests\TestSupport\TestModels\TestModel;

/**
 * This test hits the real Cloudflare edge. It only runs when a
 * CLOUDFLARE_IMAGES_ZONE and MEDIA_LIBRARY_PUBLIC_URL are set, and is skipped
 * everywhere else, so CI never needs credentials.
 */
beforeEach(function () {
    $zone = env('CLOUDFLARE_IMAGES_ZONE');

    if (! $zone) {
        $this->markTestSkipped('Set CLOUDFLARE_IMAGES_ZONE to run the live Cloudflare test.');
    }

    config()->set('media-library.image_drivers.cloudflare.zone', $zone);
});

it('really transforms an image through cloudflare', function () {
    $model = TestModel::create(['name' => 'test']);

    $media = $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection();

    $conversion = Conversion::create('cf')
        ->manipulate(fn (CloudflareImage $image) => $image->width(50)->height(50)->fit(CloudflareFit::Cover));

    $driver = app(ImageDriverManager::class)->driver('cloudflare');

    $temporaryFile = tempnam(sys_get_temp_dir(), 'cf').'.jpg';

    $driver->convert($media, $conversion, $temporaryFile);

    [$width, $height] = getimagesize($temporaryFile);

    expect($width)->toBeLessThanOrEqual(50)->and($height)->toBeLessThanOrEqual(50);

    @unlink($temporaryFile);
});
