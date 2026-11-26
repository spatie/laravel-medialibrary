---
title: Ordering media
weight: 6
---

Media Library tracks item order automatically using the `order_column` column on the `media` table. New items are appended to the end by default (oldest to newest).

To reorder existing media items, pass their IDs in the desired order to `Media::setNewOrder()`:

```php
// Assigns order_column = 1 to ID 11, 2 to ID 2, and 3 to ID 26.
// You can pass a custom starting order as the second argument.
Media::setNewOrder([11, 2, 26]);
```

You can also set an item's order when adding it with `setOrder()`:

```php
$yourModel
    ->addMedia($pathToFile)
    ->setOrder(1)
    ->toMediaCollection();
```

Or update `order_column` directly on a `Media` model:

```php
$media->order_column = 10;
$media->save();
```
