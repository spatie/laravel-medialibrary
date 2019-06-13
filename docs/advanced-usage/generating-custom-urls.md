---
title: Generating custom urls
weight: 9
---

When `getUrl` is called, the task of generating that URL is passed to an implementation of `Spatie\MediaLibraryUrlGenerator`.

The package contains a `LocalUrlGenerator` that can generate URLs for a media library that is stored inside the public path. An `S3UrlGenerator` is also included for when you're using S3 to store your files. 

If you are storing your media files in a private directory or are using a different filesystem, you can write your own `UrlGenerator`. Your generator must adhere to the `Spatie\MediaLibraryUrlGenerator` interface. If you'd extend `Spatie\MediaLibraryUrlGenerator\BaseGenerator` you only need to implement one method: `getUrl`, which should return the URL. You can call `getPathRelativeToRoot` to get the relative path to the root of your disk.

The code of the included `S3UrlGenerator` should help make things more clear:

```php
 namespace Spatie\MediaLibrary\UrlGenerator;
 
 use Spatie\MediaLibrary\Exceptions\UrlCouldNotBeDeterminedException;
 
 class S3UrlGenerator extends BaseUrlGenerator implements UrlGenerator
 {
     /**
      * Get the URL for the profile of a media item.
      *
      * @return string
      *
      * @throws UrlCouldNotBeDeterminedException
      */
     public function getUrl()
     {
         return config('laravel-medialibrary.s3.domain').'/'.$this->getPathRelativeToRoot();
     }
 }
```
