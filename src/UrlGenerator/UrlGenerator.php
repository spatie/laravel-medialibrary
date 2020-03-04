<?php

namespace Spatie\Medialibrary\UrlGenerator;

use DateTimeInterface;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\Models\Media;
use Spatie\Medialibrary\PathGenerator\PathGenerator;

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
