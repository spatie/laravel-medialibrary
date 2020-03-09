---
title: Naming conversion files
weight: 4
---

By default, all conversion files will be named in this format:

```
{original-file-name-without-extension}-{name-of-the-conversion}.{extension}
```

Should you want to name your conversion file using another format, than you can specify the class name of your own `ConversionFileNamer` in the `conversion_file_namer` key of the `media-library.php` config file.

The only requirement is that your class extends `Spatie\MediaLibrary\Conversion\ConversionFileNamer`. In your class you should implement the `getFileName` method that returns the name of the file without the extension.

Here the implementation of `Spatie\MediaLibrary\Conversion\DefaultConversionFileNamer`

```php
namespace Spatie\MediaLibrary\Conversions;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DefaultConversionFileNamer extends ConversionFileNamer
{
    public function getFileName(Conversion $conversion, Media $media): string
    {
        $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$fileName}-{$conversion->getName()}";
    }
}
```
