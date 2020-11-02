---
title: Downloading a single file
weight: 1
---

`Media` implements the `Responsable` interface. This means that you can just return a media object to download the associated file in your browser.

```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadMediaController
{
   public function show(Media $mediaItem)
   {
       return response()->download($mediaItem->getPath(), $mediaItem->file_name);
   }
}
```

## Are you a visual learner?

Here's a video that shows how to download files.

<iframe width="560" height="315" src="https://www.youtube.com/embed/cVcN03MWTb4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/videos/discovering-laravel-media-library).
