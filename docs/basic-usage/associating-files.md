---
title: Associating files
weight: 2
---

You can associate a file with a model like this:

```php
$yourModel = YourModel::find(1);
$yourModel
    ->addMedia($pathToFile)
    ->toMediaCollection();
```

The file is now associated with `$yourModel` and **moved** to the configured disk, which deletes the original file at `$pathToFile`.

If you want to keep the original file in place instead of moving it, chain `preservingOriginal()` (or use `copyMedia($pathToFile)`):

```php
$yourModel
    ->addMedia($pathToFile)
    ->preservingOriginal()
    ->toMediaCollection();
```

You can also add a remote file by URL:

```php
$url = 'https://example.com/images/mountain.jpg';
$yourModel
    ->addMediaFromUrl($url)
    ->toMediaCollection();
```

If a file already lives on one of your Laravel storage disks, add it with `addMediaFromDisk()`:

```php
$yourModel
    ->addMediaFromDisk('/path/to/file', 's3')
    ->toMediaCollection();
```

Just like `addMedia()`, `addMediaFromDisk()` deletes the source file on that disk after copying it unless you chain `preservingOriginal()`.

## Attaching media to an unsaved model

You can also call `addMedia()->toMediaCollection()` on a model that hasn't been saved yet. Media Library holds on to the pending attachment and stores the file once you call `$yourModel->save()`.

## Validating uploads

By default, Media Library blocks filenames that contain potentially executable extensions such as `.php`, `.phtml`, `.phar`, or `.htaccess` (configured via `disallowed_extensions` in `config/media-library.php`). You can also enable a strict extension allowlist with `allowed_extensions`, or restrict a collection by MIME type with `acceptsMimeTypes()` or `acceptsFile()`.

Still, whenever you accept file uploads from users, validate them in your request first using [Laravel's file validation rules](https://laravel.com/docs/validation#rule-mimes) before passing them to Media Library.

## Are you a visual learner?

Here's a video that shows how to associate files.

<iframe width="560" height="315" src="https://www.youtube.com/embed/ffcB3Anq634" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
