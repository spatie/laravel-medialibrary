<?php

namespace Spatie\Medialibrary\ImageGenerators;

use Spatie\Medialibrary\Conversion\Conversion;
use Spatie\Medialibrary\Models\Media;

interface ImageGenerator
{
    public function canConvert(Media $media);

    /**
     * Receive a file and return a thumbnail in jpg/png format.
     *
     * @param string $path
     * @param \Spatie\Medialibrary\Conversion\Conversion|null $conversion
     *
     * @return string
     */
    public function convert(string $path, Conversion $conversion = null): string;

    public function canHandleMime(string $mime = ''): bool;

    public function canHandleExtension(string $extension = ''): bool;

    public function getType(): string;
}
