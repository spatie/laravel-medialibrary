<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Media;

abstract class BaseGenerator implements ImageGenerator
{
    protected $shouldCheckMime = true;

    public function canConvert(Media $media): bool
    {
        if (! $this->requirementsAreInstalled()) {
            return false;
        }

        if ($this->supportedExtensions()->contains($media->getExtensionAttribute())) {
            return true;
        }

        if ($this->shouldCheckMime && file_exists($media->getPath())
            && $this->supportedMimetypes()->contains(File::getMimetype($media->getPath()))) {
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

    public function shouldCheckMime(bool $shouldCheckMime)
    {
        $this->shouldCheckMime = $shouldCheckMime;

        return $this;
    }

    abstract public function requirementsAreInstalled(): bool;

    abstract public function supportedExtensions(): Collection;

    abstract public function supportedMimetypes(): Collection;
}
