---
title: Ordering media
weight: 6
---

This package has a built in feature to help you order the media in your project. By default all inserted media items are ordered by their creation order (from the oldest to the newest) using the `order_column` column of the `media` table.

You can easily reorder a list of media by calling  ̀Media::setNewOrder`:

```php
 /**
  * This function reorders the records: the record with the first id in the array
  * will get order 1, the record with the second id will get order 2, ...
  *
  * A starting order number can be optionally supplied (defaults to 1).
  *
  * @param array $ids
  * @param int $startOrder
  */
Media::setNewOrder([11, 2, 26]);
```

Of course you can also manually change the value of the `order_column`.

```php
$media->order_column = 10;

$media->save();
```
