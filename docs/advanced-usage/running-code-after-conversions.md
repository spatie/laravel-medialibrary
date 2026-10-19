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

Using `then()` or `catch()` runs the media's derivatives on the queue, and the success callback fires once they finish. This is true even for conversions that would otherwise run inline.

Because the callback has to fire after everything is done, all of the media item's derivatives run inside a single job, in order. That means `queued()`, `nonQueued()` and `deferred()` on individual conversions do not apply to a media item added with `then()`. Every conversion for that item runs on the queue, inside that one job.

The job uses `media-library.queue_connection_name` and `media-library.queue_name`. Call `onQueue('images')` on the file adder to override the queue. Dispatch respects `queue_conversions_after_database_commit`; a rolled-back transaction does not dispatch its callbacks. A job whose media has since been deleted is discarded.

Configured derivative job classes supply their `handle()` methods, resolved through Laravel's container. They are not separately dispatched: their middleware, retry settings, `failed()` hooks and queue interaction methods do not control this orchestrator. Configure retries and timeouts on the worker processing the callback queue instead.

With the `sync` queue driver the derivatives run inline, and the callback still fires. This makes the feature behave predictably in local development and in tests.

`toMediaCollection()` still returns the `Media` synchronously. The callbacks are additive and do not change that.

On the `sync` driver the callback runs while `toMediaCollection()` is still executing, so the collection has not been pruned yet. If your collection uses `singleFile()` or `onlyKeepLatest()`, a `then` callback that reads the whole collection sees the item that is about to be removed. Read the `Media` the callback receives rather than re-reading the collection.

## Media without derivatives

If a media item has no conversions and no responsive images, the same job runs the `then` callback without generating derivatives. It runs inline only with the `sync` queue driver; asynchronous connections still require a worker. Virtual conversions do not generate files and are not fetched before the callback.

## Failure handling

If a derivative or the `then` callback throws, your `catch` callback runs with the throwable, on the queue worker when queued, or inline when using the `sync` driver. Returning normally from `catch` handles the error: Laravel considers the job successful and does not retry it. Rethrow the exception from `catch` to retain Laravel's retry and failed-job behavior. Timeouts and worker termination cannot reliably invoke `catch`.

If you do not register a `catch` callback, a failing derivative surfaces as a regular failed job (or, on the `sync` driver, the exception propagates as usual).

Queue delivery is not exactly once. Retries can repeat derivatives and callbacks, so make callback side effects idempotent. Failures while accepting or storing the original file, building conversions, serializing callbacks, or dispatching the job propagate to the caller; `catch` only handles exceptions inside the job.

## Serialization constraint

The callbacks are serialized so they can run on the queue. Because of this they cannot capture state that is not serializable, such as a database connection or an open file handle. Capture simple, serializable values (ids, strings, arrays) and resolve services inside the callback.
