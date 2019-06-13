---
title: Associating files
weight: 2
---

You can associate a file with a model like this:

```php
$newsItem = NewsItem::find(1);
$newsItem
   ->addMedia($pathToFile)
   ->toMediaCollection();
```

The file will now be associated with the `NewsItem` instance and will be moved to the disk you've configured.

If you want to not move, but copy, the original file you can call `preservingOriginal`:

```php
$newsItem
   ->addMedia($pathToFile)
   ->preservingOriginal()
   ->toMediaCollection();
```

You can also add a remote file to the media library:

```php
$url = 'http://medialibrary.spatie.be/assets/images/mountain.jpg';
$newsItem
   ->addMediaFromUrl($url)
   ->toMediaCollection();
```
