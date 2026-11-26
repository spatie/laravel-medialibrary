---
title: Consuming events
weight: 8
---

Media Library dispatches five events you can listen for in your application.

### `MediaHasBeenAddedEvent`

`Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent`

Dispatched after a media file has been saved to disk.

- `$event->media`: the `Media` model that was stored

### `ConversionWillStartEvent`

`Spatie\MediaLibrary\Conversions\Events\ConversionWillStartEvent`

Dispatched right before a conversion starts.

- `$event->media`: the `Media` model being converted
- `$event->conversion`: the `Conversion` instance about to run
- `$event->copiedOriginalFile`: local temporary path to the source file

### `ConversionHasBeenCompletedEvent`

`Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent`

Dispatched after a conversion finishes.

- `$event->media`: the `Media` model that was converted
- `$event->conversion`: the `Conversion` instance that completed

### `ResponsiveImagesGeneratedEvent`

`Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGeneratedEvent`

Dispatched after responsive images have been generated for a media item.

- `$event->media`: the `Media` model whose responsive images were generated

### `CollectionHasBeenClearedEvent`

`Spatie\MediaLibrary\MediaCollections\Events\CollectionHasBeenClearedEvent`

Dispatched after a collection is cleared via `clearMediaCollection()`.

- `$event->model`: the `HasMedia` model whose collection was cleared
- `$event->collectionName`: the name of the cleared collection

## Listening for events

Create a listener class in `app/Listeners`:

```php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class MediaLogger
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;
        $path = $media->getPath();

        Log::info("File {$path} has been saved for media {$media->id}");
    }
}
```

In Laravel 12+, listeners inside `app/Listeners` with a type-hinted event parameter on `handle()` are discovered automatically. If you prefer registering listeners manually, use `Event::listen()` in your `AppServiceProvider::boot()` method:

```php
use App\Listeners\MediaLogger;
use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

Event::listen(MediaHasBeenAddedEvent::class, MediaLogger::class);
```
