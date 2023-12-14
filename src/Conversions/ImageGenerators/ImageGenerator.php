<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class ImageGenerator
{
    /*
     * This function should return a path to an image representation of the given file.
     */
    abstract public function convert(string $file, ?Conversion $conversion = null): ?string;

    public function canConvert(Media $media): bool
    {
        if (! $this->requirementsAreInstalled()) {
            return false;
        }

        $validExtension = $this->canHandleExtension(strtolower($media->extension));

        $validMimeType = $this->canHandleMime(strtolower($media->mime_type));

        if ($this->shouldMatchBothExtensionsAndMimeTypes()) {
            return $validExtension && $validMimeType;
        }

        return $validExtension || $validMimeType;
    }

    public function shouldMatchBothExtensionsAndMimeTypes(): bool
    {
        return false;
    }

    public function canHandleMime(string $mime = ''): bool
    {
        return $this->supportedMimeTypes()->contains($mime);
    }

    public function canHandleExtension(string $extension = ''): bool
    {
        return $this->supportedExtensions()->contains($extension);
    }

    public function getType(): string
    {
        return strtolower(class_basename(static::class));
    }

    abstract public function requirementsAreInstalled(): bool;

    abstract public function supportedExtensions(): Collection;

    abstract public function supportedMimeTypes(): Collection;
}
