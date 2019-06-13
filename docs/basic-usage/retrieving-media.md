---
title: Retrieving media
weight: 3
---

To retrieve files you can use the `getMedia`-method:

```php
$mediaItems = $newsItem->getMedia();
```

The method returns a collection of `Media`-objects.

You can retrieve the URL and path to the file associated with the `Media`-object with `getUrl` and `getPath`:

```php
$publicUrl = $mediaItems[0]->getUrl();
$fullPathOnDisk = $mediaItems[0]->getPath();
```

A media-object also has a name, by default its filename:

```php
echo $mediaItems[0]->name; // Display the name

$mediaItems[0]->name = 'new name';
$mediaItems[0]->save(); // The new name gets saved. Activerecord ftw!
```

The name of the media-object can be changed when it's added to the media-library:

```php
$newsItem->addMedia($pathToFile)->usingName('new name')->toMediaLibrary();
```

The name of the uploaded file can be changed via the media-object:

```php
$mediaItems[0]->file_name = 'newFileName.jpg';
$mediaItems[0]->save(); // Saving will also rename the file on the filesystem.
```

The name of the uploaded file can also be changed when it gets added to the media-library:

```php
$newsItem->addMedia($pathToFile)->usingFileName('otherFileName.txt')->toMediaLibrary();
```

You can also retrieve the size of the file via the `size` and `humanReadableSize` accessors:

```php
$mediaItems[0]->size; // Returns the size in bytes
$mediaItems[0]->humanReadableSize; // Returns the size in a human readable format (eg. 1,5 MB)
```

You can remove something from the library by simply calling `delete` on the media-object:

```php
$mediaItems[0]->delete();
```

When a mediaitem gets deleted all related files will be removed from the filesystem.

Deleting a model with associated media, will also delete all associated files.

```php
$newsItem->delete(); // All associated files will be deleted as well
```

If you want to remove all associated media in a specific collection you can use the `clearMediaCollection` method:

```php
$newsItem->clearMediaCollection(); // All media will be deleted
```
