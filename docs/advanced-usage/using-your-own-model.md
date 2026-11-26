---
title: Using your own model
weight: 4
---

If you want to add custom methods, scopes, or relationships to media items, extend the default `Spatie\MediaLibrary\MediaCollections\Models\Media` model:

```php
namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    // ...
}
```

Then point `media_model` in `config/media-library.php` to your class:

```php
// config/media-library.php

'media_model' => App\Models\Media::class,
```

All `InteractsWithMedia` relationships and helpers will automatically return instances of your custom model.

## Customizing the observer

If you need to customize how media lifecycle events (creating, updating, deleting) behave, extend `Spatie\MediaLibrary\MediaCollections\Models\Observers\MediaObserver` and register it under `media_observer` in `config/media-library.php`:

```php
// config/media-library.php

'media_observer' => App\Observers\CustomMediaObserver::class,
```

## JSON serialization

By default, `MediaCollection` serializes in the UUID-keyed format expected by Media Library Pro components. If you aren't using Media Library Pro and return `$model->getMedia()` directly in JSON API responses, set `use_default_collection_serialization` to `true` in `config/media-library.php` to get standard Laravel collection serialization.
