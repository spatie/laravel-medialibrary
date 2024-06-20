<?php

namespace Spatie\MediaLibrary\Support\UrlGenerator;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

abstract class BaseUrlGenerator implements UrlGenerator
{
    protected ?Media $media = null;

    protected ?Conversion $conversion = null;

    protected ?PathGenerator $pathGenerator = null;

    public function __construct(protected Config $config) {}

    public function setMedia(Media $media): UrlGenerator
    {
        $this->media = $media;

        return $this;
    }

    public function setConversion(Conversion $conversion): UrlGenerator
    {
        $this->conversion = $conversion;

        return $this;
    }

    public function setPathGenerator(PathGenerator $pathGenerator): UrlGenerator
    {
        $this->pathGenerator = $pathGenerator;

        return $this;
    }

    public function getPathRelativeToRoot(): string
    {
        if (is_null($this->conversion)) {
            return $this->pathGenerator->getPath($this->media).($this->media->file_name);
        }

        return $this->pathGenerator->getPathForConversions($this->media)
                .$this->conversion->getConversionFile($this->media);
    }

    protected function getDiskName(): string
    {
        return $this->conversion === null
            ? $this->media->disk
            : $this->media->conversions_disk;
    }

    protected function getDisk(): Filesystem
    {
        return Storage::disk($this->getDiskName());
    }

    public function versionUrl(string $path = ''): string
    {
        if (! $this->config->get('media-library.version_urls')) {
            return $path;
        }

        return "{$path}?v={$this->media->updated_at->timestamp}";
    }
}
