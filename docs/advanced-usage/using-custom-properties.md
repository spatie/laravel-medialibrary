---
title: Using custom properties
weight: 2
---

When adding a file to the medialibrary you can pass an array with custom properties:

```php
$mediaItem = $newsItem
    ->addMedia($pathToFile)
    ->withCustomProperties(['primaryColor' => 'red'])
    ->toMediaCollection();
```

There are some methods to work with custom properties:

```php
$mediaItem->hasCustomProperty('primaryColor'); // returns true
$mediaItem->getCustomProperty('primaryColor'); // returns 'red'

$mediaItem->hasCustomProperty('does not exist'); // returns false
$mediaItem->getCustomProperty('does not exist'); // returns null

$mediaItem->setCustomProperty('name', 'value'); // adds a new custom propery
$mediaItem->forgetCustomProperty('name'); // removes a custom propery
```

It is also possible to filter a collection by a custom property using filters. These can either be a simple key value array or a callback to allow for more control:

```php
$filteredCollection = $this->model->getMedia('images', ['primaryColor' => 'red']);

$filteredCollection = $this->model->getMedia('images', function (Media $media) {
    return isset($media->custom_properties['primaryColor']);
});

```

If you are setting or removing custom properties outside the process of adding media then you will need to persist/save these changes:

```php
$mediaItem = Media::find($id);

$mediaItem->setCustomProperty('name', 'value'); // adds a new custom propery or updates an existing one
$mediaItem->forgetCustomProperty('name'); // removes a custom propery

$mediaItem->save();
```

You can also specify a default value when retrieving a custom property.

```php
$mediaItem->getCustomProperty('isPublic', false);
```

If you're dealing with nested custom properties, you can use dot notation.

```php
$mediaItem = $newsItem
    ->addMedia($pathToFile)
    ->withCustomProperties([
        'group' => ['primaryColor' => 'red']
    ])
    ->toMediaCollection();

$mediaItem->hasCustomProperty('group.primaryColor'); // returns true
$mediaItem->getCustomProperty('group.primaryColor'); // returns 'red'

$mediaItem->hasCustomProperty('nested.does-not-exist'); // returns false
$mediaItem->getCustomProperty('nested.does-not-exist'); // returns null
```
