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

The file will now be associated with the `YourModel` instance and will be moved to the disk you've configured.

If you want to not move, but copy, the original file you can call `preservingOriginal`:

```php
$yourModel
   ->addMedia($pathToFile)
   ->preservingOriginal()
   ->toMediaCollection();
```

You can also add a remote file to the media library:

```php
$url = 'http://medialibrary.spatie.be/assets/images/mountain.jpg';
$yourModel
   ->addMediaFromUrl($url)
   ->toMediaCollection();
```

If a file already exists on a storage disk, you can also add it to the media library:

```php
$yourModel
   ->addMediaFromDisk('/path/to/file', 's3')
   ->toMediaCollection();
```

The media library does not restrict what kinds of files may be uploaded or associated with models. If you are accepting file uploads from users, you should take steps to validate those uploads, to ensure you don't introduce security vulnerabilities into your project. Laravel has a [a rule to validate uploads based on MIME type or file extension](https://laravel.com/docs/validation).

## Are you a visual learner?

Here's a video that shows how to associate files.

<iframe width="560" height="315" src="https://www.youtube.com/embed/ffcB3Anq634" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/videos/discovering-laravel-media-library).
