<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Models\Media;

abstract class BaseGenerator implements ImageGenerator
{
    public function canConvert(Media $media): bool
    {
        if (! $this->requirementsAreInstalled()) {
            return false;
        }

        if ($this->supportedExtensions()->contains(strtolower($media->extension))) {
            return true;
        }

        if ($this->supportedMimetypes()->contains(strtolower($media->mime_type))) {
            return true;
        }

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
