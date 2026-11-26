---
title: Working with multiple filesystems
weight: 1
---

By default, Media Library stores all files on the disk configured in `disk_name` in `config/media-library.php`.

You can store files on [any filesystem disk configured in your Laravel app](https://laravel.com/docs/filesystem#configuration). This is handy when you want small images on a local disk and large downloads on S3.

Pass the disk name as the second argument to `toMediaCollection()`:

```php
// Stored on the 's3' disk
$yourModel->addMedia($pathToAFile)->toMediaCollection('images', 's3');
```

## Storing conversions on a separate disk

You can store [conversions](/docs/laravel-medialibrary/v11/converting-images/defining-conversions) and [responsive images](/docs/laravel-medialibrary/v11/responsive-images/getting-started-with-responsive-images) on a different disk than the original file. Pass the target disk name to `storingConversionsOnDisk()`.

In this example, the original file is stored on `local` while its conversions go to `s3`:

```php
$media = $yourModel
    ->addMedia($pathToImage)
    ->storingConversionsOnDisk('s3')
    ->toMediaCollection('images', 'local');
```

### Configuring a default conversions disk

If you want all conversions to land on a specific disk without calling `storingConversionsOnDisk()` on every upload, set `conversions_disk_name` in `config/media-library.php`:

```php
// config/media-library.php

return [
    'disk_name' => 's3',
    'conversions_disk_name' => 'public',

    // ...
];
```

Now originals stay on `s3`, while conversions and responsive images go to `public` by default.

Disk selection follows this order of precedence (highest first):

1. `->storingConversionsOnDisk(...)` on the `FileAdder`
2. `->storeConversionsOnDisk(...)` on the media collection
3. `conversions_disk_name` in `config/media-library.php`
4. The disk where the original file is stored (the default when `conversions_disk_name` is `null`)

## Are you a visual learner?

Here's a video that shows how to work with multiple filesystems.

<iframe width="560" height="315" src="https://www.youtube.com/embed/kUXKhjKvmsY" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
