---
title: Retrieving converted images
weight: 2
---

You can retrieve the url or path to a converted image by specifying the conversion name in the `getUrl`, `getTemporaryUrl` (only available when using an S3 disk) and `getPath` functions:

```php
$mediaItems = $yourModel->getMedia('images');
$mediaItems[0]->getUrl('thumb');
$mediaItems[0]->getPath('thumb'); // Absolute path on its disk
$mediaItems[0]->getTemporaryUrl(Carbon::now()->addMinutes(5), 'thumb'); // Temporary S3 url (Keep first parameter null to use default expiration time)
```

Because retrieving an url for the first media item in a collection is such a common scenario, the `getFirstMediaUrl` convenience-method is provided. The first parameter is the name of the collection, the second is the name of a conversion. There's also a `getFirstMediaPath`-variant that returns the absolute path on its disk and a `getFirstTemporaryURL`-variant which returns an temporary S3 url.

```php
$urlToFirstListImage = $yourModel->getFirstMediaUrl('images', 'thumb');
$urlToFirstTemporaryListImage = $yourModel->getFirstTemporaryUrl(Carbon::now()->addMinutes(5), 'images', 'thumb'); // Temporary S3 url (Keep first parameter null to use default expiration time)
$fullPathToFirstListImage = $yourModel->getFirstMediaPath('images', 'thumb');
```

If a conversion is queued, a file may not exist yet on the generated url. You can check if the conversion has been created using the `hasGeneratedConversion`-method on a media item.

```php
$yourModel->getMedia('images')[0]->hasGeneratedConversion('thumb'); // returns true or false
```

If a conversion does not exist, you might want to fallback to an other conversion or even the original file. This can be achieved using the `getAvailableUrl`, `getAvailableFullUrl` or `getAvailablePath` method. Each of these methods accepts an array of conversion names. It will return the url or path of the first conversion that has been generated and is available. If none of the provided conversions have been generated yet, then it will use the url or path of the original file.

```php
$mediaItems = $yourModel->getMedia('images');
$mediaItems[0]->getAvailableUrl(['small', 'medium', 'large']);
$mediaItems[0]->getAvailableFullUrl(['small', 'medium', 'large']);
$mediaItems[0]->getAvailablePath(['small', 'medium', 'large']);
```
