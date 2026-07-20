<?php

namespace Spatie\MediaLibrary\MediaCollections\Exceptions;

use Exception;

class CloudflareTransformationFailed extends Exception
{
    public static function requestFailed(string $url, int $status): self
    {
        return new static("Cloudflare returned status {$status} when transforming `{$url}`.");
    }

    public static function unsupportedMediaType(string $fileName): self
    {
        return new static("The cloudflare image driver can only convert images. `{$fileName}` is not an image.");
    }

    public static function missingZone(): self
    {
        return new static('The cloudflare image driver needs a `zone` (the base url of a Cloudflare zone with image transformations enabled). Set it in the `media-library.image_drivers` config.');
    }
}
