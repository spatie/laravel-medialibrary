<?php

namespace Spatie\MediaLibrary\Attributes;

use Attribute;
use Spatie\Image\Enums\Fit;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MediaConversion
{
    public function __construct(
        public readonly string $name,
        /** @var array<int, string> */
        public readonly array $collections = [],
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?Fit $fit = null,
        public readonly ?string $format = null,
        public readonly ?int $quality = null,
        public readonly ?bool $queued = null,
        public readonly bool $responsiveImages = false,
        public readonly bool $keepOriginalImageFormat = false,
    ) {}
}
