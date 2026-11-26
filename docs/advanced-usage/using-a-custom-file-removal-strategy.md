---
title: Using a custom file removal strategy
weight: 5
---

By default, each media item lives in its own directory named after its `id`. When you delete a `Media` model, `DefaultFileRemover` lists the files in that directory, deletes the files belonging to the media item, and removes the directory once it's empty.

If you use a custom directory structure that groups many files into shared folders, listing every file in a shared directory on every delete can be slow (especially on S3):

```text
media
└── 2026/11
    ├── file.jpg
    ├── second.jpg
    └── conversions
        ├── file-small.jpg
        ├── file-medium.jpg
        ├── file-big.jpg
        ├── second-small.jpg
        ├── second-medium.jpg
        └── second-big.jpg
```

In setups like this, you can switch to the built-in `FileBaseFileRemover` or write your own `FileRemover`.

## Using `FileBaseFileRemover`

Media Library ships with `FileBaseFileRemover`, which resolves the exact paths of the original file and its conversions and deletes only those files without scanning the parent directory:

```php
// config/media-library.php

'file_remover_class' => Spatie\MediaLibrary\Support\FileRemover\FileBaseFileRemover::class,
```

## Writing your own `FileRemover`

If you need different removal behavior altogether, implement `Spatie\MediaLibrary\Support\FileRemover\FileRemover` and register your class under `file_remover_class` in `config/media-library.php`:

```php
namespace Spatie\MediaLibrary\Support\FileRemover;

use Illuminate\Contracts\Filesystem\Factory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
