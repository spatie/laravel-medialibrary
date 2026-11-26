---
title: Generating your own tiny placeholder
weight: 4
---

When generating responsive images, Media Library creates a tiny blurred version of your image for [progressive image loading](/docs/laravel-medialibrary/v11/responsive-images/getting-started-with-responsive-images#progressive-image-loading).

If you'd rather generate a different placeholder (for example, a solid dominant color instead of a blur), implement `Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator` and register it under `responsive_images.tiny_placeholder_generator` in `config/media-library.php`.

The interface has a single method:

```php
public function generateTinyPlaceholder(string $sourceImagePath, string $tinyImageDestinationPath): void;
```

Read the source image at `$sourceImagePath` and write a tiny `jpg` placeholder to `$tinyImageDestinationPath`.

Here's the default blurred implementation for reference:

```php
namespace App\Support;

use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\MediaLibrary\Support\ImageFactory;

class Blurred implements TinyPlaceholderGenerator
{
    public function generateTinyPlaceholder(string $sourceImagePath, string $tinyImageDestinationPath): void
    {
        $sourceImage = ImageFactory::load($sourceImagePath);

        $sourceImage->width(32)->blur(5)->save($tinyImageDestinationPath);
    }
}
```
