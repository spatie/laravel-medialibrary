---
title: Generating custom URLs
weight: 9
---

When `getUrl` is called, the task of generating that URL is passed to an implementation of `Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator`.

The package contains a `DefaultUrlGenerator` that can generate URLs for a media library that is stored inside the public path. It can also generate URLs for S3.

If you are storing your media files in a private directory or are using a different filesystem, you can write your own `UrlGenerator`. Your generator must implement the `Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator` interface. 
