<?php

namespace Spatie\MediaLibrary\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MediaCollection
{
    public function __construct(
        public string $name,
        public bool $singleFile = false,
        public ?int $onlyKeepLatest = null,
        /** @var array<int, string> */
        public array $acceptsMimeTypes = [],
        public ?string $disk = null,
        public ?string $conversionsDisk = null,
        public ?string $fallbackUrl = null,
        public ?string $fallbackPath = null,
        public bool $responsiveImages = false,
    ) {}
}
