<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class ResponsiveImagesNotSupported extends Exception
{
    public static function forConversion(string $conversionName): self
    {
        return new static("Conversion `{$conversionName}` has responsive images enabled, but its image driver generates the conversion remotely and cannot produce responsive images locally. Use the `cloudflare-delivery` driver for edge responsive images, or a local driver such as `spatie`.");
    }
}
