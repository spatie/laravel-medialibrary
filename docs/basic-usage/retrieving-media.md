---
title: Retrieving media
weight: 3
---

To retrieve files you can use the `getMedia`-method:

```php
$mediaItems = $newsItem->getMedia();
```

The method returns a collection of `Media`-objects.

You can retrieve the url and path to the file associated with the `Media`-object using  `getUrl` and `getPath`:

```php
$publicUrl = $mediaItems[0]->getUrl();
$fullPathOnDisk = $mediaItems[0]->getPath();
```

An instance of `Media` also has a name, by default its filename:

```php
echo $mediaItems[0]->name; // Display the name

$mediaItems[0]->name = 'new name';
$mediaItems[0]->save(); // The new name gets saved. Activerecord ftw!
```

The name of a `Media` instance can be changed when it's added to the medialibrary:

```php
$newsItem
   ->addMedia($pathToFile)
   ->usingName('new name')
   ->toMediaCollection();
```

The name of the uploaded file can be changed via the media-object:

```php
$mediaItems[0]->file_name = 'newFileName.jpg';
$mediaItems[0]->save(); // Saving will also rename the file on the filesystem.
```

The name of the uploaded file can also be changed when it gets added to the media-library:

```php
$newsItem
   ->addMedia($pathToFile)
   ->usingFileName('otherFileName.txt')
   ->toMediaCollection();
```

You can also retrieve the size of the file via  `size` and `human_readable_size` :

```php
$mediaItems[0]->size; // Returns the size in bytes
$mediaItems[0]->human_readable_size; // Returns the size in a human readable format (eg. 1,5 MB)
```

An instance of `Media` also contains the mime type of the file.

```php
$mediaItems[0]->mime_type; // Returns the mime type
```

You can remove something from the library by simply calling `delete` on an instance of `Media`:

```php
$mediaItems[0]->delete();
```

When a `Media` instance gets deleted all related files will be removed from the filesystem.

Deleting a model with associated media, will also delete all associated files.

```php
$newsItem->delete(); // all associated files will be deleted as well
```

If you want to remove all associated media in a specific collection you can use the `clearMediaCollection` method:

```php
$newsItem->clearMediaCollection(); // all media will be deleted
```
