---
title: Retrieving converted images
weight: 2
---

You can retrieve the url or path to a converted image by specifying the conversion name in the `getUrl`, `getTemporaryUrl` (only available when using an S3 disk) and `getPath` functions:

```php
$mediaItems = $yourModel->getMedia('images');
$mediaItems[0]->getUrl('thumb');
$mediaItems[0]->getPath('thumb'); // Absolute path on its disk
$mediaItems[0]->getTemporaryUrl(Carbon::now()->addMinutes(5), 'thumb'); // Temporary S3 url
```

Because retrieving an url for the first media item in a collection is such a common scenario, the `getFirstMediaUrl` convenience-method is provided. The first parameter is the name of the collection, the second is the name of a conversion. There's also a `getFirstMediaPath`-variant that returns the absolute path on its disk and a `getFirstTemporaryURL`-variant which returns an temporary S3 url.

```php
$urlToFirstListImage = $yourModel->getFirstMediaUrl('images', 'thumb');
$urlToFirstTemporaryListImage = $yourModel->getFirstTemporaryUrl(Carbon::now()->addMinutes(5), 'images', 'thumb');
$fullPathToFirstListImage = $yourModel->getFirstMediaPath('images', 'thumb');
```

If a conversion is queued, a file may not exist yet on the generated url. You can check if the conversion has been created using the `hasGeneratedConversion`-method on a media item.

```php
$yourModel->getMedia('images')[0]->hasGeneratedConversion('thumb'); // returns true or false
```
