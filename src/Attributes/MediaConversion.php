<?php

namespace Spatie\MediaLibrary\Attributes;

use Attribute;
use BackedEnum;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Support\CollectionName;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MediaConversion
{
    /** @var array<int, string> */
    public readonly array $collections;

    /**
     * @param  array<int, BackedEnum|string>  $collections
     */
    public function __construct(
        public readonly string $name,
        array $collections = [],
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?Fit $fit = null,
        public readonly ?string $format = null,
        public readonly ?int $quality = null,
        public readonly ?bool $queued = null,
        public readonly bool $responsiveImages = false,
        public readonly bool $keepOriginalImageFormat = false,
    ) {
        $this->collections = CollectionName::resolveMany($collections);
    }
}
