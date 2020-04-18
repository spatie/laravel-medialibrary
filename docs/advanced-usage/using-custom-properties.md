---
title: Using custom properties
weight: 2
---

When adding a file to the media library you can pass an array with custom properties:

```php
$mediaItem = $yourModel
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

$mediaItem->setCustomProperty('name', 'value'); // adds a new custom property
$mediaItem->forgetCustomProperty('name'); // removes a custom property
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

$mediaItem->setCustomProperty('name', 'value'); // adds a new custom property or updates an existing one
$mediaItem->forgetCustomProperty('name'); // removes a custom property

$mediaItem->save();
```

You can also specify a default value when retrieving a custom property.

```php
$mediaItem->getCustomProperty('isPublic', false);
```

If you're dealing with nested custom properties, you can use dot notation.

```php
$mediaItem = $yourModel
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

---
title: Special custom properties
weight: 2
---

## ZIP File Folders

The ZIP export stores all media files in the root folder of the ZIP file.

If you want to save media in subfolders, you can do this with the help of the special custom property 'zip_filename_prefix'.

Each media can be assigned to a subfolder.

```php
$mediaItem = Media::find($id);

$mediaItem->setCustomProperty('zip_filename_prefix', 'folder/subfolder/'); // stores $mediaItem in Subfolder

$mediaItem->save();

$mediaStream =  MediaStream::create('export.zip');
$mediaStream->addMedia($mediaItem);
```
