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

To override this default folder structure, a class that conforms to the `PathGenerator`-interface can be specified as the `path_generator` in the config file.

Let's take a look at the interface:

```php
namespace Spatie\MediaLibrary\PathGenerator;

use Spatie\MediaLibrary\Models\Media;

interface PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return string
     */
    public function getPath(Media $media): string;

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media): string;

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return string
     */
    public function getPathForResponsiveImages(Media $media): string;
}
```

[This example from the tests](https://github.com/spatie/laravel-medialibrary/blob/7.0.0/tests/Unit/PathGenerator/CustomPathGenerator.php) uses
the md5 value of media-id to name directories. The directories where conversions are stored will be named `c` instead of the default `conversions`.

There aren't any restrictions on how the directories can be named. When a `Media`-object gets deleted the package will delete its entire associated directory. To avoid tears or worse, make sure that every media gets stored its own unique directory.
