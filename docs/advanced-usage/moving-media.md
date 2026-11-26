---
title: Moving media
weight: 7
---

You can move media from one model to another with the `move` method.

```php
$mediaItem = $model->getMedia()->first();

$movedMediaItem = $mediaItem->move($anotherModel, 'new-collection', 's3');
```

Any conversions defined on `$anotherModel` will be performed. The `name` and the `custom_properties` will be transferred as well.

## Copying media

You can also copy media from one model with the `copy` method.

```php
$mediaItem = $model->getMedia()->first();

$copiedMediaItem = $mediaItem->copy($anotherModel, 'new-collection', 's3');
```

### Copying media together with its conversions

The `copy` method generates all conversions and responsive images of the copied media from scratch. When the model you copy to defines the same conversions as the original, you can use `copyWithConversions` to copy the files that were already generated instead.

```php
$copiedMediaItem = $mediaItem->copyWithConversions($anotherModel, 'new-collection');
```

On S3 compatible disks the files are copied on the server, with multiple requests running at the same time.

Conversions are matched by name. A conversion is generated as usual when it was not generated for the original yet, when `$anotherModel` defines it with another file format, or when its file cannot be copied. All conversions are generated again when the conversions of the copy are stored on a different disk than the conversions of the original.
