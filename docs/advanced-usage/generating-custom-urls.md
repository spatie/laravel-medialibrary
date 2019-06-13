---
title: Generating custom urls
weight: 3
---

When `getUrl` is called, the task of generating that url is passed to an implementation of `Spatie\MediaLibrary\UrlGenerator`.

The package contains a `LocalUrlGenerator` that can generate urls for a media library that is stored inside the public path. An `S3UrlGenerator` is also included for when you're using S3 to store your files.

If you are storing your media files in a private directory or are using a different filesystem, you can write your own `UrlGenerator`. Your generator must adhere to the `Spatie\MediaLibrary\UrlGenerator` interface. If you'd extend `Spatie\MediaLibrary\UrlGenerator\BaseUrlGenerator` you only need to implement one method: `getUrl`, which should return the URL. You can call `getPathRelativeToRoot` to get the relative path to the root of your disk.

The code of the included `S3UrlGenerator` should help make things more clear:

```php
namespace Spatie\MediaLibrary\UrlGenerator;

class S3UrlGenerator extends BaseUrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     */
    public function getUrl() : string
    {
        return config('medialibrary.s3.domain').'/'.$this->getPathRelativeToRoot();
    }
}
```
