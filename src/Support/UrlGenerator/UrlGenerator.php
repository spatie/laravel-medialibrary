<?php

namespace Spatie\Medialibrary\Support\UrlGenerator;

use DateTimeInterface;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\MediaCollections\Models\Media;
use Spatie\Medialibrary\Support\PathGenerator\PathGenerator;

interface UrlGenerator
{
    public function getUrl(): string;

    public function getPath(): string;

    public function setMedia(Media $media): self;

    public function setConversion(Conversion $conversion): self;

    public function setPathGenerator(PathGenerator $pathGenerator): self;

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string;

    public function getResponsiveImagesDirectoryUrl(): string;
}
