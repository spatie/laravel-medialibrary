---
title: Moving media
weight: 7
---

You can move a media item from one model (or collection/disk) to another with `move()`:

```php
$mediaItem = $model->getFirstMedia();

$movedMediaItem = $mediaItem->move($anotherModel, 'new-collection', 's3');
```

Under the hood, `move()` copies the file to `$anotherModel`, creates a **new** `Media` record (with a new `id` and `uuid`), transfers `name`, `custom_properties`, and `manipulations`, runs any conversions defined on `$anotherModel`, and force-deletes the original `Media` record.

You can also pass a new filename as the fourth argument:

```php
$movedMediaItem = $mediaItem->move($anotherModel, 'new-collection', 's3', 'renamed.jpg');
```

## Copying media

To duplicate a media item onto another model while keeping the original intact, use `copy()`:

```php
$mediaItem = $model->getFirstMedia();

$copiedMediaItem = $mediaItem->copy($anotherModel, 'new-collection', 's3');
```

`copy()` also accepts an optional `$fileName` fourth argument and a fifth callback argument (`?Closure $fileAdderCallback`) if you want to tweak the underlying `FileAdder` before saving:

```php
$copiedMediaItem = $mediaItem->copy(
    $anotherModel,
    'new-collection',
    's3',
    'renamed.jpg',
    fn (FileAdder $fileAdder) => $fileAdder->withResponsiveImages(),
);
```
