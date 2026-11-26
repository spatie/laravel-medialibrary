---
title: Generating custom URLs
weight: 9
---

Whenever you call `$media->getUrl()`, Media Library delegates URL generation to the class configured under `url_generator` in `config/media-library.php`.

The default `DefaultUrlGenerator` handles public local disks and cloud disks like S3. If you store media in a private directory, serve files through a custom controller or CDN domain, or use a custom filesystem driver, you can write your own URL generator.

The easiest approach is to extend `Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator` (or `BaseUrlGenerator`) and override only the methods you need:

```php
namespace App\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class CustomUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        $url = 'https://cdn.example.com/'.$this->getPathRelativeToRoot();

        return $this->versionUrl($url);
    }
}
```

Then register your class in `config/media-library.php`:

```php
// config/media-library.php

'url_generator' => App\Support\CustomUrlGenerator::class,
```

If you need full control from scratch, implement `Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator` directly.
