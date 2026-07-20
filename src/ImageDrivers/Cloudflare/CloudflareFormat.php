<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

enum CloudflareFormat: string
{
    case Auto = 'auto';
    case Avif = 'avif';
    case Webp = 'webp';
    case Jpeg = 'jpeg';
    case BaselineJpeg = 'baseline-jpeg';

    /**
     * The file extension conversions produced in this format are stored with.
     */
    public function extension(): string
    {
        return match ($this) {
            self::Avif => 'avif',
            self::Webp => 'webp',
            self::Jpeg, self::BaselineJpeg => 'jpg',
            self::Auto => 'jpg',
        };
    }
}
