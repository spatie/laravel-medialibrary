---
title: Simple media collections
weight: 1
---

If you have different types of files that you want to associate, you can put them in their own collection.

```php
$yourModel = YourModel::find(1);
$yourModel->addMedia($pathToImage)->toMediaCollection('images');
$yourModel->addMedia($pathToAnotherImage)->toMediaCollection('images');
$yourModel->addMedia($pathToPdfFile)->toMediaCollection('downloads');
$yourModel->addMedia($pathToAnExcelFile)->toMediaCollection('downloads');
```

All media in a specific collection can be retrieved like this:

```php
// will return media instances for all files in the images collection
$yourModel->getMedia('images');

// will returns media instance for all files in the downloads collection
$yourModel->getMedia('downloads');
```

A collection can have any name you want. If you don't specify a name, the file will be added to a collection named `default`.

You can clear out a specific collection by passing the name to `clearMediaCollection`:

```php
$yourModel->clearMediaCollection('images');
```

Also, there is a `clearMediaCollectionExcept` method which can be useful if you want to remove only few or some selected media in a collection. It accepts the collection name as the first argument and the media instance or collection of media instances which should not be removed as the second argument:

```php
$yourModel->clearMediaCollectionExcept('images', $yourModel->getFirstMedia()); // This will remove all associated media in the 'images' collection except the first media
```

## Are you a visual learner?

Here's a video that shows how to work with collections.

<iframe width="560" height="315" src="https://www.youtube.com/embed/H23EGsik7xE" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use this package](https://spatie.be/videos/discovering-laravel-media-library).
