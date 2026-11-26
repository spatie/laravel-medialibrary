---
title: Using custom properties
weight: 2
---

You can attach arbitrary metadata to a media item using `withCustomProperties()`:

```php
$mediaItem = $yourModel
    ->addMedia($pathToFile)
    ->withCustomProperties(['primaryColor' => 'red'])
    ->toMediaCollection();
```

Work with custom properties on any `Media` instance using these helpers:

```php
$mediaItem->hasCustomProperty('primaryColor'); // true
$mediaItem->getCustomProperty('primaryColor'); // 'red'

$mediaItem->hasCustomProperty('does not exist'); // false
$mediaItem->getCustomProperty('does not exist'); // null

$mediaItem->setCustomProperty('name', 'value'); // adds or updates a property
$mediaItem->forgetCustomProperty('name'); // removes a property
```

You can also pass a default value to `getCustomProperty()`:

```php
$mediaItem->getCustomProperty('isPublic', false);
```

When modifying custom properties on an existing `Media` instance, remember to save the model:

```php
$mediaItem = Media::find($id);

$mediaItem->setCustomProperty('name', 'value');
$mediaItem->forgetCustomProperty('name');

$mediaItem->save();
```

## Nested properties

Dot notation works for nested arrays:

```php
$mediaItem = $yourModel
    ->addMedia($pathToFile)
    ->withCustomProperties([
        'group' => ['primaryColor' => 'red'],
    ])
    ->toMediaCollection();

$mediaItem->hasCustomProperty('group.primaryColor'); // true
$mediaItem->getCustomProperty('group.primaryColor'); // 'red'

$mediaItem->hasCustomProperty('nested.does-not-exist'); // false
$mediaItem->getCustomProperty('nested.does-not-exist'); // null
```

## Filtering collections by custom properties

Pass either a key-value array or a closure as the second argument to `getMedia()`:

```php
$filteredCollection = $yourModel->getMedia('images', ['primaryColor' => 'red']);

$filteredCollection = $yourModel->getMedia('images', function (Media $media) {
    return isset($media->custom_properties['primaryColor']);
});
```

## Special custom properties

### ZIP file folders

By default, `MediaStream` places every file in the root of the downloaded zip archive. To group files into subfolders inside the zip, set the `zip_filename_prefix` custom property:

```php
$mediaItem = Media::find($id);

$mediaItem->setCustomProperty('zip_filename_prefix', 'folder/subfolder/');
$mediaItem->save();

$mediaStream = MediaStream::create('export.zip')->addMedia($mediaItem);
```
