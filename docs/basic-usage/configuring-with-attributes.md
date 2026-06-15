---
title: Configuring with attributes
weight: 4
---

Media collections and conversions can be declared directly on your model using PHP attributes, instead of (or alongside) the `registerMediaCollections()` and `registerMediaConversions()` methods.

```php
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Attributes\MediaCollection;
use Spatie\MediaLibrary\Attributes\MediaConversion;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[MediaCollection(name: 'avatar', singleFile: true, fallbackUrl: '/default-avatar.png')]
#[MediaCollection(name: 'downloads')]
#[MediaConversion(name: 'thumb', collections: ['avatar'], width: 150, height: 150, fit: Fit::Crop, format: 'webp')]
#[MediaConversion(name: 'preview', width: 500)]
class User extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

Both attributes are repeatable, so you can declare as many collections and conversions as you need.

## MediaCollection attribute

The `#[MediaCollection]` attribute accepts the following arguments.

| Argument | Type | Description |
| --- | --- | --- |
| `name` | `string` | The name of the collection. Required. |
| `singleFile` | `bool` | Keep only the latest file in the collection. |
| `onlyKeepLatest` | `?int` | Keep only the latest N files in the collection. Takes precedence over `singleFile`. |
| `acceptsMimeTypes` | `array` | Restrict the collection to these mime types. |
| `disk` | `?string` | The disk the collection stores its files on. |
| `conversionsDisk` | `?string` | The disk the collection stores its conversions on. |
| `fallbackUrl` | `?string` | A fallback url for when the collection is empty. |
| `fallbackPath` | `?string` | A fallback path for when the collection is empty. |
| `responsiveImages` | `bool` | Generate responsive images for the collection. |

## MediaConversion attribute

The `#[MediaConversion]` attribute accepts the following arguments.

| Argument | Type | Description |
| --- | --- | --- |
| `name` | `string` | The name of the conversion. Required. |
| `collections` | `array` | The collections this conversion applies to. When omitted, it applies to all collections. |
| `width` | `?int` | The desired width. |
| `height` | `?int` | The desired height. |
| `fit` | `?Fit` | A `Spatie\Image\Enums\Fit` case. When set, the width and height are passed to the fit. |
| `format` | `?string` | The output format, such as `webp`. |
| `quality` | `?int` | The output quality. |
| `queued` | `?bool` | Whether the conversion is queued. When omitted, the package default applies. |
| `responsiveImages` | `bool` | Generate responsive images for the conversion. |
| `keepOriginalImageFormat` | `bool` | Keep the original image format instead of converting it. |

## Combining attributes with methods

Attributes and the `registerMediaCollections()` / `registerMediaConversions()` methods can be used together. Attributes are resolved first, then the methods run on top. A collection or conversion declared in a method overrides an attribute declared one with the same name. This lets you cover the common cases with attributes while keeping the methods for anything dynamic.

```php
#[MediaCollection(name: 'images')]
#[MediaConversion(name: 'thumb', width: 150, height: 150)]
class NewsItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('watermarked')->watermark(storage_path('logo.png'));
    }
}
```

## When to use methods instead

The attribute arguments cover geometry, format, and the collection level toggles. They do not expose the full set of [spatie/image](https://spatie.be/docs/image/v3) manipulations (such as `blur`, `greyscale`, `border`, or `watermark`). For those, and for conversions that depend on the `$media` instance, or collections that use `acceptsFile()` with a closure, keep using the `registerMediaConversions()` and `registerMediaCollections()` methods.
