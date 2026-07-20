<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Exceptions\CloudflareTransformationFailed;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait BuildsTransformationUrls
{
    public function imageClass(): string
    {
        return CloudflareImage::class;
    }

    public function transformationUrl(Media $media, Conversion $conversion): string
    {
        $zone = rtrim((string) ($this->config['zone'] ?? ''), '/');

        if ($zone === '') {
            throw CloudflareTransformationFailed::missingZone();
        }

        $parameterString = collect($this->cloudflareImage($conversion)->toParameters())
            ->map(fn (string|int|float $value, string $name) => "{$name}={$value}")
            ->implode(',');

        return "{$zone}/cdn-cgi/image/{$parameterString}/{$media->getFullUrl()}";
    }

    protected function cloudflareImage(Conversion $conversion): CloudflareImage
    {
        $image = new CloudflareImage;

        if ($closure = $conversion->getManipulationClosure()) {
            ($closure->getClosure())($image);
        }

        return $image;
    }
}
