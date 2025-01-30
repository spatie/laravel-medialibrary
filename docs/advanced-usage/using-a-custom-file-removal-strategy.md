---
title: Using a custom file removal strategy
weight: 5
---

By default, files will be stored inside a directory that uses the `id` of its `Media`-object as a name. Given this default file and folder structure, the `DefaultFileRemover` option simply gets the root folder name and deletes it.

In cases where you need to use a custom directory structure, the `DefaultFileRemover` can cause problems. For example, if you have a directory structure like this:


```
media
---- 2023/09
------ file.jpg
------ second.jpg
------ conversions
--------- file-small.jpg
--------- file-medium.jpg
--------- file-big.jpg
--------- second-small.jpg
--------- second-medium.jpg
--------- second-big.jpg
...
```

Using the `DefaultFileRemover` will delete the entire `2023` directory, which is not what you want. So we would use a custom file remover to delete only the files that are no longer needed.


### Extending file remover functionality


Let's take a look at the interface:

```php
<?php

namespace Programic\MediaLibrary\Support\FileRemover;

use Illuminate\Contracts\Filesystem\Factory;
use Programic\MediaLibrary\MediaCollections\Filesystem;
use Programic\MediaLibrary\MediaCollections\Models\Media;

interface FileRemover
{
    public function __construct(Filesystem $mediaFileSystem, Factory $filesystem);

    /*
     * Remove all files relating to the media model.
     */
    public function removeAllFiles(Media $media): void;

    /*
     * Remove responsive files relating to the media model.
     */
    public function removeResponsiveImages(Media $media, string $conversionName): void;

    /*
     * Remove a file relating to the media model.
     */
    public function removeFile(string $path, string $disk): void;

}

```
You may use create your own custom file remover by implementing the `FileRemover` interface.

### Here to help

There is also now a second option available within media library for file remover functionality. Based on the above directory structure, we can use `FileBaseFileRemover`.

```php
    // config/media-library.php

    /*
     * The class that contains the strategy for determining how to remove files.
     */
    'file_remover_class' => Programic\MediaLibrary\Support\FileRemover\FileBaseFileRemover::class,
```

This strategy works by locating the exact path of the image and conversions, and explicitly removing those files only, instead of purging a base directory.
