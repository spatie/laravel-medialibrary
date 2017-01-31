<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Spatie\MediaLibrary\Media;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;

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

        $urlGenerator = UrlGeneratorFactory::createForMedia($media);

        $mimeType = strtolower(File::getMimetype($media->getPath()));

        if (method_exists($urlGenerator, 'getPath')
            && file_exists($media->getPath())
            && $this->supportedMimetypes()->contains($mimeType)) {
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
