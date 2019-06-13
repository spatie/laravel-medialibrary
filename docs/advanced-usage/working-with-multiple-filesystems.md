---
title: Working with multiple filesystems
weight: 1
---

By default all files are stored on the disk specified as the `defaultFilesystem` in the config file.

Files can also be stored [on any filesystem that is configured in your Laravel app](http://laravel.com/docs/5.0/filesystem#configuration). When adding a file to the media library you can choose on which disk the file should be stored. This is useful when you have a combination of small files that should be stored locally and big files that you want to save on s3.

The `toCollectionOnMedia` and `toMediaLibraryOnDisk` functions accept a disk name as a second parameter:

```php
// Will be stored on a disk named s3
$newsItem->addMedia($pathToAFile)->toCollectionOnDisk('images', 's3');
```
