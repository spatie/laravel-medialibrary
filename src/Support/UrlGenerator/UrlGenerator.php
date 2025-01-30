<?php

namespace Programic\MediaLibrary\Support\UrlGenerator;

use DateTimeInterface;
use Programic\MediaLibrary\Conversions\Conversion;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use Programic\MediaLibrary\Support\PathGenerator\PathGenerator;

interface UrlGenerator
{
    public function getUrl(): string;

    public function getPath(): string;

    public function setMedia(Media $media): self;

    public function setConversion(Conversion $conversion): self;

    public function setPathGenerator(PathGenerator $pathGenerator): self;

    /**
     * @param  array<string, mixed>  $options
     */
    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string;

    public function getResponsiveImagesDirectoryUrl(): string;
}
