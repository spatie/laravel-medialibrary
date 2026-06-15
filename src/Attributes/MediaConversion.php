<?php

namespace Spatie\MediaLibrary\Attributes;

use Attribute;
use Spatie\Image\Enums\Fit;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MediaConversion
{
    public function __construct(
        public string $name,
        /** @var array<int, string> */
        public array $collections = [],
        public ?int $width = null,
        public ?int $height = null,
        public ?Fit $fit = null,
        public ?string $format = null,
        public ?int $quality = null,
        public ?bool $queued = null,
        public bool $responsiveImages = false,
        public bool $keepOriginalImageFormat = false,
    ) {}
}
