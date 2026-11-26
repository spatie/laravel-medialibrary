---
title: Outputting media
weight: 5
---

To stream a `Media` file to the browser with low memory usage, call `toResponse()` (download) or `toInlineResponse()` (render in the browser, such as a PDF or image):

```php
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function download(Request $request, Media $media)
{
    return $media->toResponse($request);
}

public function preview(Request $request, Media $media)
{
    return $media->toInlineResponse($request);
}
```

`toResponse()` sets `Content-Disposition: attachment`, while `toInlineResponse()` sets `Content-Disposition: inline`.

Both methods accept an optional `$conversion` name as their second argument:

```php
public function preview(Request $request, Media $media)
{
    return $media->toInlineResponse($request, 'thumb');
}
```

## Falling back when a conversion isn't ready

If a conversion might not exist yet, use `toAvailableResponse()` or `toAvailableInlineResponse()` with an ordered list of conversion names. Media Library streams the first conversion that has been generated, or falls back to the original file if none are ready:

```php
public function download(Request $request, Media $media)
{
    return $media->toAvailableResponse($request, ['thumb', 'small']);
}
```

You can adjust the streaming chunk size (in bytes) with `$media->setStreamChunkSize(1024 * 1024)`, or override `getDownloadFilename()` on a custom `Media` model to customize the filename sent to the browser.
