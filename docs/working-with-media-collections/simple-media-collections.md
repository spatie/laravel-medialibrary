---
title: Simple media collections
weight: 1
---

If you have different types of files that you want to associate, you can put them in their own collection.

```php
$newsItem = News::find(1);
$newsItem->addMedia($pathToImage)->toMediaCollection('images');
$newsItem->addMedia($pathToAnotherImage)->toMediaCollection('images');
$newsItem->addMedia($pathToPdfFile)->toMediaCollection('downloads');
$newsItem->addMedia($pathToAnExcelFile)->toMediaCollection('downloads');
```

All media in a specific collection can be retrieved like this:

```php
// will return media instances for all files in the images collection
$newsItem->getMedia('images');

// will returns media instance for all files in the downloads collection
$newsItem->getMedia('downloads');
```

A collection can have any name you want. If you don't specify a name, the file will be added to a collection named `default`.

You can clear out a specific collection by passing the name to `clearMediaCollection`:

```php
$newsItem->clearMediaCollection('images');
```

Also, there is a `clearMediaCollectionExcept` method which can be useful if you want to remove only few or some selected media in a collection. It accepts the collection name as the first argument and the media instance or collection of media instances which should not be removed as the second argument:

```php
$newsItem->clearMediaCollectionExcept('images', $newsItem->getFirstMedia()); // This will remove all associated media in the 'images' collection except the first media
```
