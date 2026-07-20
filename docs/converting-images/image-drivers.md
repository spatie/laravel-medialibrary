---
title: Image drivers
weight: 6
---

A conversion is performed by an image driver. Media Library ships three:

- `spatie` (the default): uses [spatie/image](https://spatie.be/docs/image/v3) and its `gd`, `imagick`, or `vips` engine. Generates a file on your own disk.
- `cloudflare`: Cloudflare transforms the image, and Media Library fetches the result and stores it as a normal conversion file on your disk.
- `cloudflare-delivery`: nothing is generated. The conversion is transformed at Cloudflare's edge when the url is requested.

## Writing manipulations

Manipulate the image with a closure. Type its parameter against the image object of the driver you want, and you get full autocompletion for that driver's real capabilities.

```php
use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\Fit;

$this->addMediaConversion('thumb')
    ->manipulate(fn (ImageDriver $image) => $image->fit(Fit::Crop, 300, 300)->sharpen(10));
```

The type of the closure parameter also selects the driver. A closure typed against `Spatie\Image\Drivers\ImageDriver` uses the default spatie engine. A closure typed against `CloudflareImage` uses Cloudflare.

You can still define conversions with the fluent manipulation methods (`->width(300)->height(300)`) as before. Those run on the spatie driver.

## Choosing the driver

Set the default driver in the config file. The legacy `gd`, `imagick`, and `vips` values keep working and select the spatie engine.

```php
// config/media-library.php
'image_driver' => env('MEDIA_LIBRARY_IMAGE_DRIVER', 'gd'),
```

Override it per conversion with `useImageDriver()`:

```php
$this->addMediaConversion('hero')
    ->useImageDriver('cloudflare-delivery')
    ->manipulate(fn (CloudflareImage $image) => $image->width(1600));
```

## Cloudflare

Both Cloudflare drivers share one image object, `CloudflareImage`, whose methods are Cloudflare's transformation parameters. Because they are real methods, capabilities local engines do not have (face aware cropping, background removal, automatic format negotiation) are first class and autocompleted.

```php
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;

// Mode A: Cloudflare renders it, Media Library stores the file.
$this->addMediaConversion('avatar')
    ->manipulate(fn (CloudflareImage $image) => $image
        ->width(300)->height(300)
        ->fit(CloudflareFit::Cover)
        ->gravity('face')
        ->format(CloudflareFormat::Webp)
    );

// Mode B: never generated, transformed at the edge on request.
$this->addMediaConversion('hero')
    ->useImageDriver('cloudflare-delivery')
    ->manipulate(fn (CloudflareImage $image) => $image
        ->width(1600)->quality(75)->format(CloudflareFormat::Auto)
    );
```

Set the zone (a Cloudflare zone with image transformations enabled) in the config:

```php
// config/media-library.php
'image_drivers' => [
    'cloudflare' => [
        'driver' => Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImageDriver::class,
        'zone' => env('CLOUDFLARE_IMAGES_ZONE'),
    ],
    'cloudflare-delivery' => [
        'driver' => Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareDeliveryImageDriver::class,
        'zone' => env('CLOUDFLARE_IMAGES_ZONE'),
    ],
],
```

Cloudflare needs to reach the original image, so it must live on a publicly reachable disk. The Cloudflare drivers accept declarative parameters only, so a closure typed against `CloudflareImage` cannot call local pixel operations.

### Virtual conversions

A conversion on the `cloudflare-delivery` driver is virtual: it is never generated as a file.

- `getUrl('hero')` returns the Cloudflare transformation url.
- `hasGeneratedConversion('hero')` returns true.
- `getPath('hero')` throws, because there is no file on disk.

Retrieving media does not change. `getFirstMediaUrl('images', 'hero')` returns the right url whichever driver produced the conversion.

## Registering your own driver

A driver is any class implementing `Spatie\MediaLibrary\ImageDrivers\GeneratesConversionFiles` (it produces a file) or `Spatie\MediaLibrary\ImageDrivers\ResolvesConversionUrls` (it is delivered by url). Register it with the manager:

```php
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;

public function boot(ImageDriverManager $manager): void
{
    $manager->extend('imgproxy', fn (array $config) => new ImgproxyImageDriver($config));
}
```
