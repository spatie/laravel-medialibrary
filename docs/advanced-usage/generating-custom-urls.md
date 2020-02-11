---
title: Generating custom urls
weight: 9
---

When `getUrl` is called, the task of generating that url is passed to an implementation of `Spatie\Medialibrary\UrlGenerator`.

The package contains a `DefaultUrl` that can generate urls for a media library that is stored inside the public path. It can also generate URLs for S3.

If you are storing your media files in a private directory or are using a different filesystem, you can write your own `UrlGenerator`. Your generator must adhere to the `Spatie\Medialibrary\UrlGenerator` interface. If you'd extend `Spatie\Medialibrary\UrlGenerator\BaseUrlGenerator` you only need to implement the methods: `getUrl`, `getTemporaryUrl` and `getResponsiveImagesDirectoryUrl`. You can call `getPathRelativeToRoot` to get the relative path to the root of your disk.

The code of the included `DefaultUrlGenerator` should help make things more clear:

```php
namespace Spatie\Medialibrary\UrlGenerator;

class DefaultUrlGenerator extends BaseUrlGenerator
{
    public function getUrl(): string
    {
        $url = $this->getDisk()->url($this->getPathRelativeToRoot());

        $url = $this->versionUrl($url);

        return $url;
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return $this->getDisk()->temporaryUrl($this->getPath(), $expiration, $options);
    }

    public function getBaseMediaDirectoryUrl()
    {
        return $this->getDisk()->url('/');
    }

    public function getPath(): string
    {
        $pathPrefix = $this->getDisk()->getAdapter()->getPathPrefix();

        return $pathPrefix . $this->getPathRelativeToRoot();
    }
    
    public function getResponsiveImagesDirectoryUrl(): string
    {
        $base = Str::finish($this->getBaseMediaDirectoryUrl(), '/');

        $path = $this->pathGenerator->getPathForResponsiveImages($this->media);

        return Str::finish(url($base.$path), '/');
    }
}
```
