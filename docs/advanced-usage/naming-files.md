---
title: Naming generated files
weight: 11
---

## Naming original and conversion files

By default, original files keep their filename, and conversions are named like this:

```text
{original-file-name-without-extension}-{name-of-the-conversion}.{extension}
```

To customize how files are named, extend `Spatie\MediaLibrary\Support\FileNamer\FileNamer` and register your class under `file_namer` in `config/media-library.php`.

Your subclass must implement two abstract methods (and may optionally override `originalFileName()`):

1. `conversionFileName(string $fileName, Conversion $conversion): string`: returns the conversion filename without its extension.
2. `responsiveFileName(string $fileName): string`: returns the base filename prefix for responsive images without its extension.
3. `originalFileName(string $fileName): string` *(optional)*: returns the original filename without its extension. `FileNamer` already provides a default implementation that preserves dots inside filenames (e.g. `archive.tar.gz` becomes `archive.tar`).

Here is the built-in `DefaultFileNamer` for reference:

```php
namespace Spatie\MediaLibrary\Support\FileNamer;

use Spatie\MediaLibrary\Conversions\Conversion;

class DefaultFileNamer extends FileNamer
{
    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $strippedFileName = pathinfo($fileName, PATHINFO_FILENAME);

        return "{$strippedFileName}-{$conversion->getName()}";
    }

    public function responsiveFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}
```

## Naming responsive image files

By default, responsive images are named in this format:

```text
{original-file-name-without-extension}___{name-of-the-conversion}_{width}_{height}.{extension}
```

With a custom `FileNamer`, `responsiveFileName()` lets you change the filename prefix before `___`. The rest of the filename is fixed because Media Library parses the conversion name and dimensions from it.
