<?php

namespace Spatie\Medialibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\ImageGenerators\ImageGenerator;

class Image extends ImageGenerator
{
    public function convert(string $path, Conversion $conversion = null): string
    {
        return $path;
    }

    public function requirementsAreInstalled(): bool
    {
        return true;
    }

    public function supportedExtensions(): Collection
    {
        return collect(['png', 'jpg', 'jpeg', 'gif']);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect(['image/jpeg', 'image/gif', 'image/png']);
    }
}
