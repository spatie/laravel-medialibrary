---
title: Simple media collections
weight: 1
---

When a model holds different kinds of files, you can group them into separate collections:

```php
$yourModel = YourModel::find(1);
$yourModel->addMedia($pathToImage)->toMediaCollection('images');
$yourModel->addMedia($pathToAnotherImage)->toMediaCollection('images');
$yourModel->addMedia($pathToPdfFile)->toMediaCollection('downloads');
$yourModel->addMedia($pathToAnExcelFile)->toMediaCollection('downloads');
```

Retrieve all media in a specific collection by passing its name to `getMedia()`:

```php
// returns all media in the 'images' collection
$yourModel->getMedia('images');

// returns all media in the 'downloads' collection
$yourModel->getMedia('downloads');
```

A collection can have any name you like. If you omit the name, the file goes into the `default` collection.

To delete every file in a collection, call `clearMediaCollection()`:

```php
$yourModel->clearMediaCollection('images');
```

To delete everything in a collection except one or more items, use `clearMediaCollectionExcept()`:

```php
$yourModel->clearMediaCollectionExcept('images', $yourModel->getFirstMedia('images'));
```

## Are you a visual learner?

Here's a video that shows how to work with collections.

<iframe width="560" height="315" src="https://www.youtube.com/embed/H23EGsik7xE" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
