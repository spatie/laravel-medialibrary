---
title: Retrieving converted images
weight: 2
---

Pass a conversion name to `getUrl()`, `getFullUrl()`, `getTemporaryUrl()`, or `getPath()` on a `Media` model to retrieve the converted image:

```php
$mediaItems = $yourModel->getMedia('images');
$mediaItems[0]->getUrl('thumb');
$mediaItems[0]->getFullUrl('thumb'); // URL including domain
$mediaItems[0]->getPath('thumb'); // Absolute path on its disk
$mediaItems[0]->getTemporaryUrl(Carbon::now()->addMinutes(5), 'thumb'); // Pass null as the first argument to use the default lifetime
```

`getTemporaryUrl()` works on any disk that supports temporary URLs in Laravel (such as S3, GCS, or a local disk with `'serve' => true`).

You can also fetch the first or last converted item directly from your model:

```php
$urlToFirstListImage = $yourModel->getFirstMediaUrl('images', 'thumb');
$urlToFirstTemporaryListImage = $yourModel->getFirstTemporaryUrl(Carbon::now()->addMinutes(5), 'images', 'thumb');
$fullPathToFirstListImage = $yourModel->getFirstMediaPath('images', 'thumb');

$urlToLastListImage = $yourModel->getLastMediaUrl('images', 'thumb');
$urlToLastTemporaryListImage = $yourModel->getLastTemporaryUrl(Carbon::now()->addMinutes(5), 'images', 'thumb');
$fullPathToLastListImage = $yourModel->getLastMediaPath('images', 'thumb');
```

## Checking whether a queued conversion is ready

When conversions are queued, the converted file might not exist yet right after upload. Check whether it has finished with `hasGeneratedConversion()`:

```php
$yourModel->getMedia('images')[0]->hasGeneratedConversion('thumb'); // true or false
```

There is an important difference between calling URL helpers on your **model** versus on a **`Media` instance**:

- `$yourModel->getFirstMediaUrl('images', 'thumb')` checks `hasGeneratedConversion('thumb')` automatically and falls back to the original file URL if the conversion isn't ready yet.
- `$media->getUrl('thumb')` does **not** check whether the conversion exists and will return a URL that 404s until the job completes.

When working directly with a `Media` instance, use `getAvailableUrl()`, `getAvailableFullUrl()`, `getAvailablePath()`, or `getAvailableTemporaryUrl()` to fall back cleanly. Each method accepts an array of conversion names and returns the first one that has been generated, or falls back to the original file if none are ready yet:

```php
$mediaItems = $yourModel->getMedia('images');
$mediaItems[0]->getAvailableUrl(['small', 'medium', 'large']);
$mediaItems[0]->getAvailableFullUrl(['small', 'medium', 'large']);
$mediaItems[0]->getAvailablePath(['small', 'medium', 'large']);
$mediaItems[0]->getAvailableTemporaryUrl(['small', 'medium', 'large'], Carbon::now()->addMinutes(5));
```
