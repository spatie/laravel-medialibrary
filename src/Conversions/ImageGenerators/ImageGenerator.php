<?php

namespace Spatie\Medialibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\Medialibrary\MediaCollections\Models\Media;

abstract class ImageGenerator
{
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
        return $this->supportedMimetypes()->contains($mime);
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

    abstract public function supportedMimetypes(): Collection;
}
