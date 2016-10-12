<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Media;

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

        if (method_exists($media, 'getPath') && file_exists($media->getPath())
            && $this->supportedMimetypes()->contains(strtolower(File::getMimetype($media->getPath())))) {
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
