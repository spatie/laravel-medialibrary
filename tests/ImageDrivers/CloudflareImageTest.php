<?php

use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;

it('accumulates transformation parameters', function () {
    $image = new CloudflareImage;

    $image->width(300)->height(200)->fit(CloudflareFit::Cover)->gravity('face');

    expect($image->toParameters())->toBe([
        'fit' => 'cover',
        'gravity' => 'face',
        'height' => 200,
        'width' => 300,
    ]);
});

it('exposes format and quality as native parameters', function () {
    $image = new CloudflareImage;

    $image->format(CloudflareFormat::Avif)->quality(70);

    expect($image->toParameters())->toBe([
        'format' => 'avif',
        'quality' => 70,
    ]);
});

it('supports arbitrary parameters through the escape hatch', function () {
    $image = new CloudflareImage;

    $image->parameter('sharpen', 2)->parameter('trim', '10;10;10;10');

    expect($image->toParameters())->toBe([
        'sharpen' => 2,
        'trim' => '10;10;10;10',
    ]);
});

it('maps each format to the extension it is stored with', function () {
    expect(CloudflareFormat::Webp->extension())->toBe('webp')
        ->and(CloudflareFormat::Avif->extension())->toBe('avif')
        ->and(CloudflareFormat::Jpeg->extension())->toBe('jpg')
        ->and(CloudflareFormat::Auto->extension())->toBe('jpg');
});
