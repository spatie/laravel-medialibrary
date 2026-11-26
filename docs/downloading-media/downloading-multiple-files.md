---
title: Downloading multiple files
weight: 2
---

Want to let users download multiple files at once without building a zip archive on disk first? `MediaStream` streams a zip archive on the fly, even when the files live on different disks:

```php
use Spatie\MediaLibrary\Support\MediaStream;

class DownloadMediaController
{
    public function download(YourModel $yourModel)
    {
        $downloads = $yourModel->getMedia('downloads');

        // Streams the files directly into a zip download without buffering everything in memory.
        return MediaStream::create('my-files.zip')->addMedia($downloads);
    }
}
```

If two files in the stream share the same filename, `MediaStream` appends a numeric suffix (`file (1).jpg`, `file (2).jpg`) automatically. To organize files into subfolders inside the zip, set the [`zip_filename_prefix` custom property](/docs/laravel-medialibrary/v11/advanced-usage/using-custom-properties#zip-file-folders) on each media item.

## Customizing ZipStream options

You can pass custom options to the underlying `ZipStream` instance with `useZipOptions()`. See the [ZipStream-PHP options guide](https://maennchen.dev/ZipStream-PHP/guide/Options.html) for everything you can configure.

```php
use Spatie\MediaLibrary\Support\MediaStream;

class DownloadMediaController
{
    public function download(YourModel $yourModel)
    {
        $downloads = $yourModel->getMedia('downloads');

        return MediaStream::create('my-files.zip')
            ->useZipOptions(function (array &$zipOptions) {
                $zipOptions['defaultEnableZeroHeader'] = true;
            })
            ->addMedia($downloads);
    }
}
```

## Are you a visual learner?

Here's a video that shows how to download files.

<iframe width="560" height="315" src="https://www.youtube.com/embed/cVcN03MWTb4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
