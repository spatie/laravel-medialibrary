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

## Coping media

You can copy media from one model with the `copy` method.

```php
$mediaItem = $model->getMedia()->first();

$copiedMediaItem = $mediaItem->copy($anotherModel, 'new-collection', 's3');
```
This will transferred `name` and the `custom_properties` keeping the media in the original model
