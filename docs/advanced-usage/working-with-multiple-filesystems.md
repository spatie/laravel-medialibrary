---
title: Working with multiple filesystems
weight: 1
---

By default, all files are stored on the disk specified as the `disk_name` in the config file.

Files can also be stored [on any filesystem that is configured in your Laravel app](http://laravel.com/docs/7.x/filesystem#configuration). When adding a file to the media library you can choose on which disk the file should be stored. This is useful when you have a combination of small files that should be stored locally and big files that you want to save on S3.

`toMediaCollection` accepts a disk name as a second parameter:

```php
// Will be stored on a disk named s3
$yourModel->addMedia($pathToAFile)->toMediaCollection('images', 's3');
```

## Storing conversions on a separate disk

You can let the media library store [your conversions](/v9/converting-images/defining-conversions) and [responsive images](/v9/responsive-images/getting-started-with-responsive-images) on a disk other than the one where you save the original item. Pass the name of the disk where you want conversion to be saved to the `storingConversionsOnDisk` method.

Here's an example where the original file is saved on the local disk and the conversions on S3.

```php
$media = $yourModel
   ->addMedia($pathToImage)
   ->storingConversionsOnDisk('s3')
   ->toMediaCollection('images', 'local');
```

