---
title: Adding custom properties
---

When adding a file to the medialibrary you can pass an array with custom properties:

```php
$mediaItem = $newsItem
    ->addMedia($pathToFile)
    ->withCustomProperties(['mime-type' => 'image/jpeg'])
    ->toMediaLibrary();
```

<span class="badge">v3.3+</span> There are some convenience methods to work with custom properties:

```php
$mediaItem->hasCustomProperty('mime-type'); // returns true
$mediaItem->getCustomProperty('mime-type'); // returns 'image/jpeg'

$mediaItem->hasCustomProperty('does not exists'); // returns false
$mediaItem->getCustomProperty('does not exists'); // returns null
```

<span class="badge">v3.5+</span> You can also specify a default value when retrieving a custom property.

```php
$mediaItem->getCustomProperty('isPublic', false);
```
