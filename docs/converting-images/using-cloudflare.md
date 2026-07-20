---
title: Using Cloudflare
weight: 7
---

Media Library can let Cloudflare perform your image conversions instead of a local engine like GD, Imagick, or libvips. This is useful on serverless hosting where image extensions are not available, and it gives you Cloudflare features that local engines do not have, such as face aware cropping, background removal, and automatic format negotiation.

There are two ways to use it:

- **Store the result** (`cloudflare`): Cloudflare transforms the image and Media Library fetches the result and stores it as a normal conversion file on your disk. Everything downstream (urls, regeneration, zip downloads) works as usual.
- **Transform on request** (`cloudflare-delivery`): nothing is generated or stored. The conversion url points at Cloudflare, which transforms the image at its edge when the url is requested.

## Setup

Follow these steps once.

### 1. Enable transformations on your zone

In the Cloudflare dashboard, go to **Images** and then **Transformations**, select the zone (a domain that is proxied through Cloudflare), and enable transformations on it. This is what makes the `/cdn-cgi/image/` delivery urls work for that domain.

### 2. Make sure Cloudflare can reach your originals

Cloudflare fetches the original image over its public url and transforms it. This means:

- The original must live on a publicly reachable disk. An image on a local disk that is not reachable from the internet, for example during local development, cannot be transformed.
- By default Cloudflare only pulls source images from the same zone where transformations are served. If your originals live somewhere else, such as an S3 or R2 bucket on a different domain, add that origin under **Define source origins** in the same Transformations screen.

### 3. Configure the zone

The zone is the base url of the domain from step 1, for example `https://your-site.com`. Media Library builds delivery urls like `https://your-site.com/cdn-cgi/image/width=300,fit=cover/<original url>`.

Set it in the config file:

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

```dotenv
CLOUDFLARE_IMAGES_ZONE=https://your-zone.example.com
```

## Defining Cloudflare conversions

Both drivers share one image object, `CloudflareImage`. Its methods are exactly Cloudflare's transformation parameters, so what you can do (and only what you can do) is autocompleted.

### Store the result

Type the manipulation closure against `CloudflareImage` and Media Library uses the `cloudflare` driver. After the media is added, the conversion is a normal file on your disk.

```php
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFit;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareFormat;
use Spatie\MediaLibrary\ImageDrivers\Cloudflare\CloudflareImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('avatar')
        ->manipulate(fn (CloudflareImage $image) => $image
            ->width(300)
            ->height(300)
            ->fit(CloudflareFit::Cover)
            ->gravity('face')
            ->format(CloudflareFormat::Webp)
        );
}
```

```php
$media->getUrl('avatar');  // a url to the stored webp file on your disk
$media->getPath('avatar'); // the path to that file
```

### Transform on request

Pick the `cloudflare-delivery` driver to skip generation entirely. The conversion is virtual: no file, no queued job.

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('hero')
        ->useImageDriver('cloudflare-delivery')
        ->manipulate(fn (CloudflareImage $image) => $image
            ->width(1600)
            ->quality(75)
            ->format(CloudflareFormat::Auto)
        );
}
```

```php
$media->getUrl('hero');  // https://your-zone.example.com/cdn-cgi/image/.../<original url>
$media->getPath('hero'); // throws: a virtual conversion has no file
```

`hasGeneratedConversion('hero')` returns true, and retrieving the conversion url works exactly like any other conversion, so your views do not need to know which driver produced it.

Because a virtual conversion is only a url, new conversions are available instantly with no generation cost, and responsive srcsets are just the same url at different widths.

## Available manipulations

`CloudflareImage` covers Cloudflare's parameters, including `width`, `height`, `fit` (a `CloudflareFit` case), `gravity` (for example `'auto'` or `'face'`), `format` (a `CloudflareFormat` case), `quality`, `blur`, `sharpen`, `brightness`, `contrast`, `saturation`, `rotate`, `flip`, `dpr`, `background`, `segment` (background removal), `upscale`, and `zoom`. For any parameter without a dedicated method, use `->parameter('name', 'value')`.

## Good to know

- The Cloudflare drivers accept declarative parameters only. A closure typed against `CloudflareImage` cannot call local pixel operations. If you need those, use the spatie driver for that conversion.
- Set the output format and quality on the `CloudflareImage` object, not with the conversion's `format()` and `quality()` methods.
- For the `cloudflare` (store the result) driver, the stored file's extension follows the `format()` you set (for example `webp`), or the original extension when you do not set a format.
