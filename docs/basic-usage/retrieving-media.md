---
title: Retrieving media
weight: 3
---

To retrieve files from the default collection, use `getMedia()`:

```php
$mediaItems = $yourModel->getMedia();
```

Pass `*` to retrieve files across all collections:

```php
$mediaItems = $yourModel->getMedia('*');
```

Both return a collection of `Media` models. To check whether a model has any media in a collection, use `hasMedia()`:

```php
$yourModel->hasMedia(); // checks the 'default' collection
$yourModel->hasMedia('images');
```

## URLs and paths

You can get the URL or disk path of a `Media` model with `getUrl()`, `getFullUrl()`, `getTemporaryUrl()`, and `getPath()`:

```php
$publicUrl = $mediaItems[0]->getUrl();
$publicFullUrl = $mediaItems[0]->getFullUrl(); // URL including domain
$fullPathOnDisk = $mediaItems[0]->getPath();
$temporaryUrl = $mediaItems[0]->getTemporaryUrl(Carbon::now()->addMinutes(5)); // Pass null to use temporary_url_default_lifetime
```

`getTemporaryUrl()` works with any disk that supports temporary URLs in Laravel (such as S3, GCS, or a local disk with `'serve' => true`).

If you want cache-busting query strings (`?v=...` based on `updated_at`), set `version_urls` to `true` in `config/media-library.php`.

Since grabbing the first or last item is so common, there are shortcuts on your model:

```php
$firstMedia = $yourModel->getFirstMedia();
$firstUrl = $yourModel->getFirstMediaUrl();

$lastMedia = $yourModel->getLastMedia();
$lastUrl = $yourModel->getLastMediaUrl();
```

## Names and filenames

Every `Media` model has a `name` (by default the original filename without its extension) and a `file_name` (the actual filename stored on disk).

```php
echo $mediaItems[0]->name;

$mediaItems[0]->name = 'new name';
$mediaItems[0]->save();
```

You can set the name when adding a file:

```php
$yourModel
    ->addMedia($pathToFile)
    ->usingName('new name')
    ->toMediaCollection();
```

Changing `file_name` on an existing `Media` model also renames the file on disk when you save:

```php
$mediaItems[0]->file_name = 'newFileName.jpg';
$mediaItems[0]->save();
```

Or set the filename upfront when adding the file:

```php
$yourModel
    ->addMedia($pathToFile)
    ->usingFileName('otherFileName.txt')
    ->toMediaCollection();
```

You can also customize filename sanitization with a callable:

```php
$yourModel
    ->addMedia($pathToFile)
    ->sanitizingFileName(function ($fileName) {
        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
    })
    ->toMediaCollection();
```

Keep in mind that Media Library's default sanitizer blocks uploads with executable extensions like `.php` or `.phtml` anywhere in the filename (so `malicious.php.jpg` is rejected too). Passing your own callable to `sanitizingFileName()` replaces the default sanitizer completely, so make sure your callback doesn't let unsafe filenames through.

You can customize the blocked extensions, or opt in to an allowlist, in `config/media-library.php`:

```php
// Reject these extensions anywhere in the filename.
'disallowed_extensions' => ['php', 'phtml', 'phar', 'htaccess', /* ... */],

// When set, only accept uploads whose final extension is in this list.
'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
```

## Size and MIME type

```php
$mediaItems[0]->size; // Size in bytes
$mediaItems[0]->human_readable_size; // Human-readable size (e.g. "1.5 MB")
$mediaItems[0]->mime_type; // MIME type (e.g. "image/jpeg")
```

## Deleting media

Delete a single media item by calling `delete()` on it. All related files (the original, conversions, and responsive images) are removed from disk as well:

```php
$mediaItems[0]->delete();
```

Deleting a model deletes all of its associated media too:

```php
$yourModel->delete(); // deletes associated files as well
```

If your model uses Laravel's `SoftDeletes` trait, calling `delete()` leaves associated files alone, while `forceDelete()` removes them.

To delete a model while keeping its media records and files, call `deletePreservingMedia()`:

```php
$yourModel->deletePreservingMedia();
```

To delete every file in a collection, use `clearMediaCollection()`:

```php
$yourModel->clearMediaCollection(); // clears the 'default' collection

$yourModel->clearMediaCollection('images'); // clears the 'images' collection
```

To delete everything in a collection except one or more items, use `clearMediaCollectionExcept()`:

```php
$yourModel->clearMediaCollectionExcept('images', $yourModel->getFirstMedia('images'));
```

## Are you a visual learner?

Here are a few videos on adding and retrieving media.

<iframe width="560" height="315" src="https://www.youtube.com/embed/ffcB3Anq634" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

<iframe width="560" height="315" src="https://www.youtube.com/embed/9bP5FCFLfSo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

<iframe width="560" height="315" src="https://www.youtube.com/embed/0liOx4T8RmY" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like these? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
