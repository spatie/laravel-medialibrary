---
title: Using a custom directory structure
weight: 5
---

By default, files will be stored inside a directory that uses the `id` of its `Media`-object as a name. Converted images will be stored inside a directory named `conversions`.

```
media
---- 1
------ file.jpg
------ conversions
--------- small.jpg
--------- medium.jpg
--------- big.jpg
---- 2
------ file.jpg
------ conversions
--------- small.jpg
--------- medium.jpg
--------- big.jpg
...
```

Putting files inside their own folders guarantees that files with the same name can be added without any problems.

To override this default folder structure, a class that conforms to the `PathGenerator`-interface can be specified as the `path_generator` in the config file. The given class will be loaded through the Laravel [Service Container](https://laravel.com/docs/container), so feel free to type-hint any dependencies in the constructor.

Let's take a look at the interface:

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

There aren't any restrictions on how the directories can be named. When a `Media`-object gets deleted the package will delete its entire associated directory. To avoid tears or worse, make sure that every media gets stored its own unique directory.

### Model-specific Custom Path Generators
In addition to setting a global path generator in the config file, You can also define a `CustomPathGenerator` class for specific models directly inside the model's `booting()` method or within a service provider:

```php
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Spatie\MediaLibrary\Tests\Support\PathGenerator\CustomPathGenerator;

class YourModel extends Model
{
    protected static function booting(): void
    {
        PathGeneratorFactory::setCustomPathGenerators(static::class, CustomPathGenerator::class);
    }
}
```

This allows you to customize the directory structure on a per-model basis.

Keep in mind that path generators set in the model override those defined in the config file.


### Defining a Custom Path Generator Inside a Model or Service Provider

 This approach allows for fine-grained control over the media directory structure on a per-model basis, without affecting global configuration.

## Are you a visual learner?

Here's a video that shows custom paths:

<iframe width="560" height="315" src="https://www.youtube.com/embed/hCXtDyGcPSo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).
