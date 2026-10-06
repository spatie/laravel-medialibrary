<?php

namespace Spatie\MediaLibrary\Tests\TestSupport;

use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator;

class TestInterfaceUrlGenerator implements UrlGenerator
{
    protected Media $media;

    protected ?Conversion $conversion = null;

    protected PathGenerator $pathGenerator;

    public function getUrl(): string
    {
        return Storage::disk($this->getDiskName())->url($this->getPathRelativeToRoot());
    }

    public function getPath(): string
    {
        return Storage::disk($this->getDiskName())->path($this->getPathRelativeToRoot());
    }

    public function getPathRelativeToRoot(): string
    {
        if (is_null($this->conversion)) {
            return $this->pathGenerator->getPath($this->media).$this->media->file_name;
        }

        return $this->pathGenerator->getPathForConversions($this->media)
            .$this->conversion->getConversionFile($this->media);
    }

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

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return $this->getUrl();
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
        return Storage::disk($this->getDiskName())->url($this->pathGenerator->getPathForResponsiveImages($this->media));
    }

    protected function getDiskName(): string
    {
        return is_null($this->conversion)
            ? $this->media->disk
            : $this->media->conversions_disk;
    }
}
