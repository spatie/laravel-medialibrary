---
title: Generating your own tiny placeholder
weight: 4
---

When generating responsive images the medialibrary will generate a tiny version of your image which will be used for [progressive image loading](/laravel-medialibrary/v7/responsive-images/getting-started-with-responsive-images#progressive-image-loading). By default this tiny version will be blurred version of the original.

You can customize how the tiny version of the image should be generated. Maybe you want a to just use the dominant color instead of blur. In the  `responsive_images.tiny_placeholder_generator` of the `medialibrary` config file you can specify a class that implements `Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator`. This interface only requires you to implement one function:

```php
public function generateTinyPlaceholder(string $sourceImagePath, string $tinyImageDestinationPath);
```

`$sourceImagePath` contains the path of the image where you should generate a tiny representation for. The generated tiny image should be saved at `$tinyImageDestinationPath`. This tiny image should be a `jpg`.

Here's a an example implementation that generates a blurred version.

```php
namespace App;

use Spatie\Image\Image;

class Blurred implements TinyPlaceholderGenerator
{
    public function generateTinyPlaceholder(string $sourceImagePath, string $tinyImageDestinationPath)
    {
        $sourceImage = Image::load($sourceImagePath);

        $sourceImage->width(32)->blur(5)->save($tinyImageDestinationPath);
    }
}
```
