<?php

namespace Spatie\MediaLibrary\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MediaCollection
{
    public function __construct(
        public readonly string $name,
        public readonly bool $singleFile = false,
        public readonly ?int $onlyKeepLatest = null,
        /** @var array<int, string> */
        public readonly array $acceptsMimeTypes = [],
        public readonly ?string $disk = null,
        public readonly ?string $conversionsDisk = null,
        public readonly ?string $fallbackUrl = null,
        public readonly ?string $fallbackPath = null,
        public readonly bool $responsiveImages = false,
    ) {}
}
