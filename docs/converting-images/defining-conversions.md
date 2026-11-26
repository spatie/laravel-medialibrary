---
title: Defining conversions
weight: 1
---

When you add files to Media Library, it can automatically create derived versions such as thumbnails and banners.

Conversions run automatically for `jpg`, `png`, `gif`, `svg`, `webp`, `avif`, `pdf`, `mp4`, `m4v`, `mov`, and `webm` files (plus `tiff`, `heic`, and `heif` when using the `imagick` driver). By default, converted images are saved as `jpg`. You can change the output format with `format()` or preserve the source image's format with `keepOriginalImageFormat()`.

Under the hood, Media Library uses [spatie/image](https://spatie.be/docs/image/v3) for manipulations, so you can chain [any manipulation method](https://spatie.be/docs/image/v3) from that package.

Check [the image generator docs](/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators) for extra requirements when generating thumbnails from PDFs, SVGs, or videos.

## Are you a visual learner?

Here's a video that shows how to work with conversions.

<iframe width="560" height="315" src="https://www.youtube.com/embed/1i-HTyyEmvM" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).

## A single conversion

Add a `registerMediaConversions()` method to your model and define your conversion inside it:

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10);
    }
}
```

Now add an image:

```php
$media = YourModel::first()->addMedia($pathToImage)->toMediaCollection();
```

Alongside the original file, Media Library creates the `thumb` conversion:

```php
$media->getPath(); // path to the original image
$media->getPath('thumb'); // path to the converted 368x232 image

$media->getUrl(); // URL to the original image
$media->getUrl('thumb'); // URL to the converted 368x232 image
```

To keep the source image's format instead of converting to `jpg`, chain `keepOriginalImageFormat()` (or pass a specific format to `format('webp')`):

```php
$this->addMediaConversion('thumb')
    ->width(368)
    ->height(232)
    ->keepOriginalImageFormat();
```

## Using multiple conversions

You can register as many conversions as you need:

```php
// in your model
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\CropPosition;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// ...

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(368)
        ->height(232)
        ->sharpen(10);

    $this->addMediaConversion('old-picture')
        ->sepia()
        ->border(10, BorderType::Overlay, 'black');

    $this->addMediaConversion('thumb-cropped')
        ->crop(400, 400, CropPosition::Center);
}
```

Retrieve each conversion by name:

```php
$media->getUrl('thumb'); // URL to the thumbnail
$media->getUrl('old-picture'); // URL to the sepia, bordered version
```

## Performing conversions on specific collections

By default, a conversion runs on files in every [collection](/docs/laravel-medialibrary/v11/working-with-media-collections/simple-media-collections). To restrict a conversion to specific collections, chain `performOnCollections()`:

```php
// in your model
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->performOnCollections('images', 'downloads')
        ->width(368)
        ->height(232);
}
```

```php
// a thumbnail is generated for this media item
$media = $yourModel->addMedia($pathToImage)->toMediaCollection('images');
$media->hasGeneratedConversion('thumb'); // true (once processed)
$media->getUrl('thumb'); // URL to the thumbnail

// no thumbnail is generated for this collection
$media = $yourModel->addMedia($pathToImage)->toMediaCollection('other-collection');
$media->hasGeneratedConversion('thumb'); // false
```

Note that `$media->getUrl('thumb')` does not check whether a conversion was generated. If you need a safe fallback to the original file, use `$media->getAvailableUrl(['thumb'])` or `$yourModel->getFirstMediaUrl('other-collection', 'thumb')`.

## Using a specific disk

You can store a collection's conversions on a different disk than its originals:

```php
public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('big-files')
        ->useDisk('s3')
        ->storeConversionsOnDisk('public');
}
```

## Queuing conversions

By default, conversions run on the queue connection and queue name configured in [`config/media-library.php`](/docs/laravel-medialibrary/v11/installation-setup). If you want a conversion to run immediately during the request, chain `nonQueued()`:

```php
// in your model
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(368)
        ->height(232)
        ->nonQueued();
}
```

If you set `queue_conversions_by_default` to `false` in `config/media-library.php`, all conversions run synchronously by default. You can still opt individual conversions back onto the queue with `queued()`:

```php
// in your model
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(368)
        ->height(232)
        ->queued();
}
```

By default, queued conversions are dispatched **after all database transactions commit**. This avoids race conditions where a queue worker tries to fetch a media record before the surrounding transaction finishes. If you want conversions dispatched immediately inside a transaction, set `queue_conversions_after_database_commit` to `false` in `config/media-library.php`.

## Deferred conversions

Instead of running a conversion synchronously or sending it to a queue, you can chain `deferred()` to run it right after the HTTP response is sent to the browser. Under the hood, this uses Laravel's [`defer()` helper](https://laravel.com/docs/helpers#deferred-functions).

Deferred conversions are handy when you want a thumbnail ready quickly without slowing down the upload request (like a user avatar).

```php
// in your model
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(368)
        ->height(232)
        ->deferred();
}
```

Deferred conversions still run in the same PHP process after the response flushes, so they keep that worker busy until they finish. For heavy conversions or models with many conversions, stick with `queued()` so a queue worker handles them.

## Using model properties in a conversion

Inside `registerMediaConversions()`, you don't have access to your model's attributes by default because Media Library instantiates an empty model to collect conversion definitions. If your conversion needs properties from the actual model instance, set `$registerMediaConversionsUsingModelInstance = true` on your model:

```php
// in your model
public bool $registerMediaConversionsUsingModelInstance = true;

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->performOnCollections('images', 'downloads')
        ->width($this->width)
        ->height($this->height);
}
```

Keep in mind that this adds extra database queries, because Media Library has to load the owning model for each media item it processes.
