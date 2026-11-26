---
title: Preparing your model
weight: 1
---

To associate media with a model, implement the `HasMedia` interface and use the `InteractsWithMedia` trait:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

That's all you need to start attaching files to your model.

## Preview thumbnails for Media Library Pro

If you use [Media Library Pro](https://medialibrary.pro)'s `Collection` component (or read `$media->preview_url`), add a `preview` conversion to your model so it can show a thumbnail for each item:

```php
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function registerMediaConversions(?Media $media = null): void
{
    $this
        ->addMediaConversion('preview')
        ->fit(Fit::Contain, 300, 300)
        ->nonQueued();
}
```
