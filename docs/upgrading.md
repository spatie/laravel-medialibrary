---
title: Upgrading
weight: 5
---

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v11 to v12

### Requirements

- PHP 8.4 or higher is now required.
- Laravel 12 or higher is now required.
- Pest 4 is now required for running the test suite (was `^2.36|^3.0|^4.0`).
- PHPStan/Larastan 3 is now required.

### Removed deprecated methods

Several deprecated method aliases on `FileAdder` have been removed. Update your code to use the current method names:

- `setName()` has been removed. Use `usingName()` instead.
- `setFileName()` has been removed. Use `usingFileName()` instead.
- `toMediaLibrary()` has been removed. Use `toMediaCollection()` instead.
- `withAttributes()` has been removed. Use `withProperties()` instead.

### Changed parameter order for temporary URLs

The parameter order of `getFirstTemporaryUrl()` and `getLastTemporaryUrl()` has changed. The new signature is `($collectionName, $conversionName, $expiration)` instead of `($expiration, $collectionName, $conversionName)`.

Before:

```php
$url = $model->getFirstTemporaryUrl($expiration, 'images', 'thumb');
```

After:

```php
$url = $model->getFirstTemporaryUrl('images', 'thumb', $expiration);
```

### UUIDs are now v7

UUIDs generated for media items are now v7 (time-ordered) instead of v4. This should not require any changes in your application, but if you are relying on the UUID format in any way, be aware of this change.

### New features

#### AVIF support in responsive images

AVIF format is now supported in responsive image generation.

#### Focal point support

You can now set a focal point on media items, which can be used during image conversions to ensure the important part of the image is preserved.

```php
$media->setFocalPoint(70, 30)->save();

$media->getFocalPoint(); // returns ['x' => 70, 'y' => 30]
$media->hasFocalPoint(); // returns true
```

To use the focal point during conversions, call `useFocalPoint()` on the conversion:

```php
$this->addMediaConversion('thumb')
    ->width(368)
    ->height(232)
    ->useFocalPoint();
```

See the [defining conversions](/docs/laravel-medialibrary/v12/converting-images/defining-conversions) and [custom properties](/docs/laravel-medialibrary/v12/advanced-usage/using-custom-properties) documentation for more details.

#### Parent model touch on media changes

The parent model's `updated_at` timestamp is now automatically touched when media is added, updated, or deleted.

---

To upgrade from older versions, read [UPGRADING.md in the laravel-medialibrary repo](https://github.com/spatie/laravel-medialibrary/blob/master/UPGRADING.md).
