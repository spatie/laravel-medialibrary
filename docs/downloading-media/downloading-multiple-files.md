---
title: Downloading multiple files
weight: 2
---

You might want to let users be able to download multiple files at once. Traditionally you'd have to create a zip archive that contains the requested files.

The media library is able to zip stream multiple files on the fly. So you don't need to create a zip archive on your server.

The provided `MediaStream` class that allows you to respond with a stream. Files will be zipped on the fly and you can even include files from multiple filesystems.

Here's an example on how it can be used:

```php
use Spatie\MediaLibrary\Support\MediaStream;

class DownloadMediaController
{
   public function download(YourModel $yourModel)
   {
        // Let's get some media.
        $downloads = $yourModel->getMedia('downloads');

        // Download the files associated with the media in a streamed way.
        // No prob if your files are very large.
        return MediaStream::create('my-files.zip')->addMedia($downloads);
   }
}
```

You can also pass any custom options to the `ZipStream` instance using the `useZipOptions` method.

All the available options are listed on the [ZipStream-PHP wiki](https://github.com/maennchen/ZipStream-PHP/wiki/Available-options).

Here's an example on how it can be used:

```php
use Spatie\MediaLibrary\Support\MediaStream;
use ZipStream\Option\Archive as ArchiveOptions;

class DownloadMediaController
{
   public function download(YourModel $yourModel)
   {
        // Let's get some media.
        $downloads = $yourModel->getMedia('downloads');

        // Download the files associated with the media in a streamed way.
        // No prob if your files are very large.
        return MediaStream::create('my-files.zip')
            ->useZipOptions(function(ArchiveOptions $zipOptions) {
                $zipOptions->setZeroHeader(true);
            })
            ->addMedia($downloads);
   }
}
```

## Are you a visual learner?

Here's a video that shows how to download files.

<iframe width="560" height="315" src="https://www.youtube.com/embed/cVcN03MWTb4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/videos/discovering-laravel-media-library).
