---
title: Naming conversion files
weight: 4
---

By default, all conversion files will be named in this format:

```
{original-file-name-without-extension}-{name-of-the-conversion}.{extension}
```

Should you want to name your conversion file using another format, than you can specify your the class name of your own `ConversionFileNamer` in the `conversion_file_namer` key of the `medialibrary.php` config file.

The only requirement is that your class implements `Spatie\Medialibrary\Conversion\ConversionFileNamer`. This is what it looks like:

```php
namespace Spatie\Medialibrary\Conversion;

use Spatie\Medialibrary\Models\Media;

interface ConversionFileNamer
{
    public function getName(Conversion $conversion, Media $media);
}
```

You can look to the code of the `Spatie\Medialibrary\Conversion\DefaultConversionFileNamer` to see an example implementation.
