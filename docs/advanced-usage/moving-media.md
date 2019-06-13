---
title: Moving media
weight: 7
---

You can move media from one model to another with the `move` method.

```php
$mediaItem = $model->getMedia()->first();

$movedMediaItem = $mediaItem->move($anotherModel);
```

Any conversions defined on `$anotherModel` will be performed. The `name` and the `custom_properties` will be transferred as well.