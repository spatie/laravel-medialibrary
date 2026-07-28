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

You can still define conversions with the fluent manipulation methods (`->width(300)->height(300)`) as before. Those run on the spatie driver. When you use both, the fluent methods are applied first and the closure runs after them, so the closure can refine what they set up.

### Setting the output format

On the spatie driver, set the output format with `format()` on the conversion rather than inside the closure.

```php
$this->addMediaConversion('thumb')
    ->format('webp')
    ->manipulate(fn (ImageDriver $image) => $image->fit(Fit::Crop, 300, 300));
```

A conversion file is named before it is generated, because urls have to resolve without reading the file. The format that name was derived from therefore has the final say, and a `format()` call inside the closure is ignored. On the Cloudflare drivers the format is part of the transformation itself, so there you do set it on the `CloudflareImage` object.

## Choosing the driver

Set the default driver in the config file. The legacy `gd`, `imagick`, and `vips` values keep working and select the spatie engine.

```php
// config/media-library.php
'image_driver' => env('IMAGE_DRIVER', 'gd'),
```

Override it per conversion with `useImageDriver()`:

```php
$this->addMediaConversion('hero')
    ->useImageDriver('cloudflare-delivery')
    ->manipulate(fn (CloudflareImage $image) => $image->width(1600));
```

## Cloudflare

The `cloudflare` and `cloudflare-delivery` drivers let Cloudflare perform conversions, either storing the result on your disk or transforming on request at the edge. See the dedicated [using Cloudflare](/docs/laravel-medialibrary/v11/converting-images/using-cloudflare) page.

## Registering your own driver

A driver is any class implementing `Spatie\MediaLibrary\ImageDrivers\GeneratesConversionFiles` (it produces a file) or `Spatie\MediaLibrary\ImageDrivers\ResolvesConversionUrls` (it is delivered by url). Register it with the manager:

```php
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;

public function boot(ImageDriverManager $manager): void
{
    $manager->extend('imgproxy', fn (array $config) => new ImgproxyImageDriver($config));
}
```

Referencing a driver name that is neither built in nor registered (in the config's `image_driver`, or through `useImageDriver()`) throws an `InvalidImageDriver` exception.
