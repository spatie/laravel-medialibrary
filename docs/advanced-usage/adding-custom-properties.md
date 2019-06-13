---
title: Adding custom properties
---

When adding a file to the medialibrary you can pass an array with custom properties:

```php
$mediaItem = $newsItem
    ->addMedia($pathToFile)
    ->withCustomProperties(['primaryColor' => 'red'])
    ->toMediaCollection();
```

There are some convenience methods to work with custom properties:

```php
$mediaItem->hasCustomProperty('primaryColor'); // returns true
$mediaItem->getCustomProperty('primaryColor'); // returns 'red'

$mediaItem->hasCustomProperty('does not exists'); // returns false
$mediaItem->getCustomProperty('does not exists'); // returns null
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
