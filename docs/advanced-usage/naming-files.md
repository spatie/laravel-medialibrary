---
title: Naming generated files
weight: 11
---

### Naming original and conversion files


By default, all original files will retain the original name. All converted files will be named in this format:

```
{original-file-name-without-extension}-{name-of-the-conversion}.{extension}
```

If you want to use a different formatting to name your original or converted file(s),
you can specify the class name of your own `FileNamer` under the `file_namer` key
within the `media-library.php` config file.

The only requirement is that your class extends `Spatie\MediaLibrary\Support\FileNamer\FileNamer`.
In your class you should implement 3 methods:
1. `originalFileName` should return the name you'd like for the original file. Return the name without the extension.
2. `conversionFileName` should return the media file name combined with the conversion name
3. `responsiveFileName` should return the media file name

Here is the implementation of `Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer`

```php
namespace Spatie\MediaLibrary\Support\FileNamer;

use Spatie\MediaLibrary\Conversions\Conversion;

class DefaultFileNamer extends FileNamer
{
    public function originalFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }

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

Just like the naming of converted files, you can use another format for naming your files
by using your own `FileNamer` class. It is only possible to prefix the name, because other parts are needed in processing responsive images.
