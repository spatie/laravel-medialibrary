---
title: Downloading a single file
weight: 1
---

`Media` implements the `Responsable` interface. This means that you can just return a media object to download the associated file in your browser.

```php
use Spatie\MediaLibrary\Models\Media;

class DownloadMediaController
{
   public function show(Media $mediaItem)
   {
      return $mediaItem;
   }
}
```

If you need more control you could also do the above more verbose:

```php
use Spatie\MediaLibrary\Models\Media;

class DownloadMediaController
{
   public function show(Media $mediaItem)
   {
       return response()->download($mediaItem->getPath(), $mediaItem->file_name);
   }
}
```
