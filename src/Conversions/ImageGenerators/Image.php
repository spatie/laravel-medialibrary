<?php

namespace Spatie\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;

class Image extends ImageGenerator
{
    public function convert(string $path, ?Conversion $conversion = null): string
    {
        return $path;
    }

    public function requirementsAreInstalled(): bool
    {
        return true;
    }

    public function supportedExtensions(): Collection
    {
        $extensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'avif'];
        if ($this->usesImagick()) {
            $extensions[] = 'tiff';
            $extensions[] = 'heic';
            $extensions[] = 'heif';
        }

        return collect($extensions);
    }

    public function supportedMimeTypes(): Collection
    {
        $mimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/webp', 'image/avif'];
        if ($this->usesImagick()) {
            $mimeTypes[] = 'image/tiff';
            $mimeTypes[] = 'image/heic';
            $mimeTypes[] = 'image/heif';
        }

        return collect($mimeTypes);
    }

    protected function usesImagick(): bool
    {
        return app(ImageDriverManager::class)->spatieEngine() === 'imagick';
    }
}
