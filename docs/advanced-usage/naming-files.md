---
title: Naming generated files
weight: 11
---

### Naming conversion files

By default, all conversion files will be named in this format:

```
{original-file-name-without-extension}-{name-of-the-conversion}.{extension}
```

Should you want to name your conversion file using another format,
then you can specify the class name of your own `FileNamer` in the `file_namer` key
of the `media-library.php` config file.

The only requirements is that your class extends `Spatie\MediaLibrary\Support\FileNamer`.
In your class you should implement 2 methods:
1. `conversionFileName` should return the media file name combined with the conversion name
2. `responsiveFileName` should return the media file name

Here is the implementation of `Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer`

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

### Naming responsive image files

By default, all responsive image files will be named in this format:

```
{original-file-name-without-extension}___{name-of-the-conversion}_{width}_{height}.{extension}
```

Just like the conversion file names, you can use another format for naming your files
by using your own `FileNamer` class. It is only possible to prefix the name, because other parts are needed in processing responsive images.
