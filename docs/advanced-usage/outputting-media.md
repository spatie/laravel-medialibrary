---
title: Outputting media
weight: 5
---

If you want to output your `$media` instance to the browser, you may use the `toResponse` & `toInlineResponse` methods.

```php
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function download(Request $request, Media $media)
{
    return $media->toResponse($request);
}
```

By using `toResponse`, your browser is instructed to download the file with the `attachment` Content-Disposition.

If you want to output your `$media` instance to your browser, but want to inline render it, you may use
`toInlineResponse`

```php
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function download(Request $request, Media $media)
{
    return $media->toInlineResponse($request);
}
```

The `toInlineResponse` method instructs your browser to inline render the file by setting the Content-Disposition to
`inline`.

Both methods use a streaming adapter to ensure low memory usage.

If you want to output a conversion of the media, both methods accept an optional argument `$conversion`.

```php
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function download(Request $request, Media $media)
{
    return $media->toInlineResponse($request, 'thumb');
}
```

If a conversion does not exist, you might want to fall back to another conversion or even the original file. This can be
achieved using the `toAvailableResponse` or `toAvailableInlineResponse` method. Each of these methods accepts an
array of conversion names. It will stream the content of the first conversion that has been generated and is
available. If none of the provided conversions have been generated yet, then it will use the original file.

```php
use Illuminate\Http\Request;use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function download(Request $request, Media $media)
{
    return $media->toAvailableResponse($request, ['thumb', 'small']);
}
```
