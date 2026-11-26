---
title: Defining media collections
weight: 2
---

A media collection can do more than [group files by name](/docs/laravel-medialibrary/v11/working-with-media-collections/simple-media-collections). By defining collections on your model, you can configure validation rules, dedicated disks, fallbacks, size limits, and conversions per collection.

Add a `registerMediaCollections` method to [your prepared model](/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model) and call `addMediaCollection()` for each collection you want to configure:

```php
// in your model

public function registerMediaCollections(): void
{
    $this->addMediaCollection('my-collection')
        // chain options here

    // define as many collections as you need
    $this->addMediaCollection('my-other-collection')
        // chain options here
}
```

## Are you a visual learner?

Here's a video that shows how to work with media collections.

<iframe width="560" height="315" src="https://www.youtube.com/embed/UmM3R9Mp6hc" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).

## Getting registered media collections

Use `getRegisteredMediaCollections()` to inspect the collections registered on a model:

```php
$mediaCollections = $yourModel->getRegisteredMediaCollections();
```

This returns a collection of `MediaCollection` objects.

## Defining a fallback URL or path

When a collection is empty, `getFirstMediaUrl()` and `getFirstMediaPath()` return an empty string (`''`). You can configure a default fallback with `useFallbackUrl()` and `useFallbackPath()`:

```php
public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('avatars')
        ->useFallbackUrl('/images/anonymous-user.jpg')
        ->useFallbackPath(public_path('/images/anonymous-user.jpg'));
}
```

By default, [conversions](/docs/laravel-medialibrary/v11/converting-images/defining-conversions) share the collection's fallback URL/path when no media exists. Pass a conversion name as the second argument to set a fallback for a specific conversion:

```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// ...

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('avatar')
        ->useFallbackUrl('/default_avatar.jpg')
        ->useFallbackUrl('/default_avatar_thumb.jpg', 'thumb')
        ->useFallbackPath(public_path('/default_avatar.jpg'))
        ->useFallbackPath(public_path('/default_avatar_thumb.jpg'), 'thumb')
        ->registerMediaConversions(function (?Media $media = null) {
            $this
                ->addMediaConversion('thumb')
                ->width(50)
                ->height(50);

            $this
                ->addMediaConversion('thumb_2')
                ->width(100)
                ->height(100);
        });
}
```

Now each conversion returns the right fallback:

```php
$yourModel->getFirstMediaUrl('avatar'); // /default_avatar.jpg
$yourModel->getFirstMediaUrl('avatar', 'thumb'); // /default_avatar_thumb.jpg
$yourModel->getFirstMediaUrl('avatar', 'thumb_2'); // /default_avatar.jpg

$yourModel->getFirstMediaPath('avatar'); // .../default_avatar.jpg
$yourModel->getFirstMediaPath('avatar', 'thumb'); // .../default_avatar_thumb.jpg
$yourModel->getFirstMediaPath('avatar', 'thumb_2'); // .../default_avatar.jpg
```

## Only allowing certain files in a collection

Pass a callback to `acceptsFile()` to decide whether a file may be added to the collection. The callback receives a `Spatie\MediaLibrary\MediaCollections\File` instance (exposing `$file->name`, `$file->size`, and `$file->mimeType`) and the owning model as an optional second argument.

In this example, we only accept JPEG files:

```php
use Spatie\MediaLibrary\MediaCollections\File;

// ...

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('only-jpegs-please')
        ->acceptsFile(function (File $file) {
            return $file->mimeType === 'image/jpeg';
        });
}
```

This succeeds:

```php
$yourModel->addMedia('beautiful.jpg')->toMediaCollection('only-jpegs-please');
```

This throws `Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection`:

```php
$yourModel->addMedia('ugly.ppt')->toMediaCollection('only-jpegs-please');
```

## Only allowing certain MIME types in a collection

If you only need to check MIME types, `acceptsMimeTypes()` is shorter than writing an `acceptsFile()` callback:

```php
public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('only-jpegs-please')
        ->acceptsMimeTypes(['image/jpeg']);
}
```

This succeeds:

```php
$yourModel->addMedia('beautiful.jpg')->toMediaCollection('only-jpegs-please');
```

This throws `Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection`:

```php
$yourModel->addMedia('ugly.ppt')->toMediaCollection('only-jpegs-please');
```

## Using a specific disk

You can tie a collection to a specific disk so files added to it always land there:

```php
// in your model

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('big-files')
        ->useDisk('s3');
}
```

Files added to `big-files` are now stored on `s3`:

```php
$yourModel->addMedia($pathToFile)->toMediaCollection('big-files');
```

You can still override the disk for a single upload by passing a disk name as the second argument:

```php
$yourModel->addMedia($pathToFile)->toMediaCollection('big-files', 'alternative-disk');
```

## Single file collections

If a collection should only ever hold one file (like a user's avatar), call `singleFile()`:

```php
// in your model

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('avatar')
        ->singleFile();
}
```

The first file is stored as usual:

```php
$yourModel->addMedia($pathToImage)->toMediaCollection('avatar');
$yourModel->getMedia('avatar')->count(); // 1
$yourModel->getFirstMediaUrl('avatar'); // URL to $pathToImage
```

When you add another file to a single-file collection, the previous file is deleted after the new one is saved:

```php
$yourModel->addMedia($anotherPathToImage)->toMediaCollection('avatar');
$yourModel->getMedia('avatar')->count(); // 1
$yourModel->getFirstMediaUrl('avatar'); // URL to $anotherPathToImage
```

This video shows a demo of a single-file collection.

<iframe width="560" height="315" src="https://www.youtube.com/embed/OBj89PI4ho4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## Limited file collections

To cap how many files a collection keeps, use `onlyKeepLatest($n)`. Once a collection exceeds that limit, Media Library deletes the oldest items (by primary key) after storing the new file:

```php
// in your model

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('limited-collection')
        ->onlyKeepLatest(3);
}
```

The first three files are all kept. When you add a fourth file, the first one is removed:

```php
$yourModel->addMedia($firstFile)->toMediaCollection('limited-collection');
$yourModel->getMedia('limited-collection')->count(); // 1

$yourModel->addMedia($secondFile)->toMediaCollection('limited-collection');
$yourModel->getMedia('limited-collection')->count(); // 2

$yourModel->addMedia($thirdFile)->toMediaCollection('limited-collection');
$yourModel->getMedia('limited-collection')->count(); // 3

$yourModel->addMedia($fourthFile)->toMediaCollection('limited-collection');
$yourModel->getMedia('limited-collection')->count(); // 3
$yourModel->getFirstMediaUrl('limited-collection'); // URL to $secondFile
```

## Registering media conversions

If you haven't read [defining conversions](/docs/laravel-medialibrary/v11/converting-images/defining-conversions) yet, start there first.

While conversions are usually defined in `registerMediaConversions()` on your model, you can also attach them directly to a media collection:

```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// ...

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('my-collection')
        ->registerMediaConversions(function (?Media $media = null) {
            $this
                ->addMediaConversion('thumb')
                ->width(100)
                ->height(100);
        });
}
```

Any image added to `my-collection` now gets a 100x100 `thumb` conversion:

```php
$yourModel->addMedia($pathToImage)->toMediaCollection('my-collection');

$yourModel->getFirstMediaUrl('my-collection', 'thumb'); // URL to the 100x100 thumbnail
```

Check [defining conversions](/docs/laravel-medialibrary/v11/converting-images/defining-conversions) for all the methods you can chain onto `addMediaConversion()`.

## Generating responsive images

To generate responsive images automatically for every image added to a collection, chain `withResponsiveImages()`:

```php
// in your model

public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('my-collection')
        ->withResponsiveImages();
}
```
