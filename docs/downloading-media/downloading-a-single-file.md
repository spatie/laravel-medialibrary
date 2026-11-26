---
title: Downloading a single file
weight: 1
---

`Media` implements Laravel's `Responsable` interface, so you can return a `Media` instance straight from a controller to stream it as a download:

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

If you want to render the file inline in the browser (for example, opening a PDF in a tab) or stream a specific conversion, see [outputting media](/docs/laravel-medialibrary/v11/advanced-usage/outputting-media).

For full control over a local file response, you can also return Laravel's download helper directly:

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

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
