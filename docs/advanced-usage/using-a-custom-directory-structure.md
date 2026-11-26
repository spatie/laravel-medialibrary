---
title: Using a custom directory structure
weight: 5
---

By default, each media item is stored in its own directory named after its `id`, with conversions placed in a `conversions` subdirectory:

```text
media
├── 1
│   ├── file.jpg
│   └── conversions
│       ├── file-small.jpg
│       ├── file-medium.jpg
│       └── file-big.jpg
└── 2
    ├── file.jpg
    └── conversions
        ├── file-small.jpg
        ├── file-medium.jpg
        └── file-big.jpg
```

Giving each media item its own directory guarantees that two files with the same name never collide.

## Writing a custom `PathGenerator`

To change this directory layout, create a class that implements `Spatie\MediaLibrary\Support\PathGenerator\PathGenerator` and register it under `path_generator` in `config/media-library.php`. The class is resolved through Laravel's [service container](https://laravel.com/docs/container), so you can type-hint constructor dependencies.

```php
namespace Spatie\MediaLibrary\Support\PathGenerator;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string;

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string;

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string;
}
```

Give every media item its own unique directory if you can. If your custom path generator stores multiple media items inside a shared directory (like `2026/11/`), consider switching to [`FileBaseFileRemover`](/docs/laravel-medialibrary/v11/advanced-usage/using-a-custom-file-removal-strategy) so deleting one item doesn't scan shared folders unnecessarily.

> **Heads up about `media-library:clean`:** The `media-library:clean` command treats every top-level directory below your configured `prefix` that isn't used by a current media path as orphaned. Use a dedicated disk or `prefix` for media, and always preview with `--dry-run` when introducing a custom path generator.

## Model-specific path generators

You can use a different `PathGenerator` per model without changing the global default. Either map models (or morph map aliases) in `config/media-library.php`:

```php
// config/media-library.php

'custom_path_generators' => [
    App\Models\YourModel::class => App\Support\PathGenerator\CustomPathGenerator::class,
    // or using a morph map alias:
    // 'your_model' => App\Support\PathGenerator\CustomPathGenerator::class,
],
```

Or register it dynamically in a model's `booting()` method or a service provider (which takes precedence over the config file):

```php
use App\Support\PathGenerator\CustomPathGenerator;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class YourModel extends Model
{
    protected static function booting(): void
    {
        PathGeneratorFactory::setCustomPathGenerators(static::class, CustomPathGenerator::class);
    }
}
```

## Moving files when a `Media` model is updated

If your custom `PathGenerator` builds paths from attributes that can change after upload (such as `collection_name`, `uuid`, or a custom property), set `moves_media_on_update` to `true` in `config/media-library.php` so Media Library moves the stored files when the path changes.

## Are you a visual learner?

Here's a video that shows custom paths.

<iframe width="560" height="315" src="https://www.youtube.com/embed/hCXtDyGcPSo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
