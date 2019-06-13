---
title: Responding with media
---

`Media` implements the `Responsable` interface. So you can just return a media object to download the associated file in your browser.

```php
use Spatie\MediaLibrary\Media;

class DownloadMediaController
{
   public function show(Media $mediaItem)
   {
      return $mediaItem;
   }
}
```
