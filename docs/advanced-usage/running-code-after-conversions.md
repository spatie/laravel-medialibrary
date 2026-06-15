---
title: Running code after conversions
weight: 20
---

When you add media, you can register a callback that runs after the media item's derivatives (its conversions and responsive images) have been generated, and a callback to handle failures.

```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

$model->addMedia($request->file('avatar'))
    ->then(function (Media $media) {
        // Runs after the derivatives have been generated.
    })
    ->catch(function (Throwable $exception) {
        // Runs if derivative generation fails.
    })
    ->toMediaCollection('avatars');
```

The `then` callback receives the `Media`. The `catch` callback receives the `Throwable`.

## Derivatives run on the queue

Using `then()` runs the media's derivatives on the queue, and the callback fires once they finish. This is true even for conversions that would otherwise run inline. It is the same idea as queueing the work and continuing afterwards.

With the `sync` queue driver the derivatives run inline, and the callback still fires. This makes the feature behave predictably in local development and in tests.

`toMediaCollection()` still returns the `Media` synchronously. The callbacks are additive and do not change that.

## Media without derivatives

If a media item has no conversions and no responsive images, the `then` callback fires immediately with the media.

## Failure handling on the sync driver

On a real queue, a failing derivative is handled in the worker, and your `catch` callback runs there. On the `sync` driver, the callback still runs, but the underlying exception also propagates out of `toMediaCollection()` (this is how the sync queue behaves). In production, where derivatives run on a real queue, this propagation does not happen.

## Serialization constraint

The callbacks are serialized so they can run on the queue. Because of this they cannot capture state that is not serializable, such as a database connection or an open file handle. Capture simple, serializable values (ids, strings, arrays) and resolve services inside the callback.
