<?php

use Spatie\Image\Drivers\ImageDriver;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareDeliveryImageDriver;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImageDriver;
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;
use Spatie\MediaLibrary\ImageDrivers\MediaImageDriver;
use Spatie\MediaLibrary\ImageDrivers\SpatieImageDriver;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidImageDriver;

beforeEach(fn () => $this->manager = new ImageDriverManager);

it('resolves the shipped drivers by name', function () {
    expect($this->manager->driver('spatie'))->toBeInstanceOf(SpatieImageDriver::class)
        ->and($this->manager->driver('cloudflare'))->toBeInstanceOf(CloudflareImageDriver::class)
        ->and($this->manager->driver('cloudflare-delivery'))->toBeInstanceOf(CloudflareDeliveryImageDriver::class);
});

it('maps the legacy image_driver engine values to the spatie driver', function () {
    config()->set('media-library.image_driver', 'imagick');

    expect($this->manager->defaultDriverName())->toBe('spatie')
        ->and($this->manager->spatieEngine())->toBe('imagick');
});

it('uses a non engine image_driver value as the default driver name', function () {
    config()->set('media-library.image_driver', 'cloudflare');

    expect($this->manager->defaultDriverName())->toBe('cloudflare');
});

it('throws for an unknown driver', function () {
    $this->manager->driver('does-not-exist');
})->throws(InvalidImageDriver::class);

it('lets you register a custom driver', function () {
    $custom = new class implements MediaImageDriver
    {
        public function imageClass(): string
        {
            return 'stdClass';
        }
    };

    $this->manager->extend('custom', fn () => $custom);

    expect($this->manager->driver('custom'))->toBe($custom);
});

it('infers a custom registered driver from the manipulation closure parameter type', function () {
    $custom = new class implements MediaImageDriver
    {
        public function imageClass(): string
        {
            return ArrayObject::class;
        }
    };

    $this->manager->extend('custom', fn () => $custom);

    $conversion = Conversion::create('a')
        ->manipulate(fn (ArrayObject $image) => $image);

    expect($this->manager->forConversion($conversion))->toBe($custom);
});

it('infers the driver from the manipulation closure parameter type', function () {
    $spatieConversion = Conversion::create('a')
        ->manipulate(fn (ImageDriver $image) => $image);

    $cloudflareConversion = Conversion::create('b')
        ->manipulate(fn (CloudflareImage $image) => $image);

    expect($this->manager->forConversion($spatieConversion))->toBeInstanceOf(SpatieImageDriver::class)
        ->and($this->manager->forConversion($cloudflareConversion))->toBeInstanceOf(CloudflareImageDriver::class);
});

it('lets an explicit driver override the inferred one', function () {
    $conversion = Conversion::create('b')
        ->useImageDriver('cloudflare-delivery')
        ->manipulate(fn (CloudflareImage $image) => $image);

    expect($this->manager->forConversion($conversion))->toBeInstanceOf(CloudflareDeliveryImageDriver::class)
        ->and($this->manager->isVirtual($conversion))->toBeTrue();
});

it('does not treat a file generating driver as virtual', function () {
    $conversion = Conversion::create('a')->useImageDriver('spatie');

    expect($this->manager->isVirtual($conversion))->toBeFalse();
});
