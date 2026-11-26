---
title: Creating a custom image generator
weight: 2
---

If you want thumbnails for a file type that isn't supported out of the box, you can write your own image generator.

Image generators only produce image representations (such as thumbnails) of a file; converting files into non-image formats isn't supported.

In this example, we'll create a generator that converts PowerPoint presentations into images.

## Writing the generator

Extend `Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator` and implement the required methods:

```php
namespace App\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

class PowerPoint extends ImageGenerator
{
    /**
     * Return a path to an image representation of the given file.
     */
    public function convert(string $file, ?Conversion $conversion = null): string
    {
        $pathToImageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        // Convert the file to an image and save it to $pathToImageFile.
        \PowerPoint::convertFileToImage($file)->store($pathToImageFile);

        return $pathToImageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return true;
    }

    public function supportedExtensions(): Collection
    {
        return collect(['ppt', 'pptx']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect([
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }
}
```

By default, a generator matches a file if **either** its extension or its MIME type matches. If you want to require **both** to match (useful when handling untrusted uploads), override `shouldMatchBothExtensionsAndMimeTypes()`:

```php
public function shouldMatchBothExtensionsAndMimeTypes(): bool
{
    return true;
}
```

## Registering your generator

Add your generator class to the `image_generators` array in `config/media-library.php`:

```php
// config/media-library.php
'image_generators' => [
    // ...
    App\ImageGenerators\PowerPoint::class,
],
```

## Passing configuration to a custom generator

You can also pass constructor arguments when registering your generator in `config/media-library.php`:

```php
// config/media-library.php
'image_generators' => [
    // ...
    App\ImageGenerators\PowerPoint::class => [
        'myArgument' => 'value',
        'myOtherArgument' => 'otherValue',
    ],
],
```

Accept those arguments in your generator's constructor:

```php
class PowerPoint extends ImageGenerator
{
    public function __construct(
        protected string $myArgument,
        protected string $myOtherArgument,
    ) {}

    // ...
}
```
