---
title: Using a custom directory structure
weight: 6
---

<span class="badge">v3.9+</span>

By default files will be stored inside a directory that uses
the `id` of it's `Media`-object as a name. Converted images will be stored inside a directory
names conversions.

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

Putting files inside their own folders guaranties that files with the same name can be added without any problems.

To override the default folder structure, a class that conforms to the `PathGenerator`-interface can be specified as the `custom_path_generator_class` in the config file.

Let's take a look at the interface:

```php
namespace Spatie\MediaLibrary\PathGenerator;

use Spatie\MediaLibrary\Media;

interface PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getPath(Media $media);

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media);
}
```

[This example from the tests](https://github.com/spatie/laravel-medialibrary/blob/3.9.0/tests/PathGenerator/CustomPathGenerator.php) uses
the md5 value of media-id to name directories. The directories where conversions are stored will be named `c` instead of the default `conversions`.

There aren't any restrictions on how the directories can be named. When a `Media`-object gets deleted the package will delete its entire associated directory.
So make sure that every media gets it's own unique directory.
