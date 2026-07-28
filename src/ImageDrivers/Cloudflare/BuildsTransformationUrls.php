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

    public function transformationUrl(Media $media, Conversion $conversion, ?int $widthOverride = null): string
    {
        $parameters = $this->transformationParameters($conversion);

        if ($widthOverride !== null) {
            $parameters['width'] = $widthOverride;
            ksort($parameters);
        }

        return $this->transformationUrlForParameters($parameters, $media->getFullUrl());
    }

    /**
     * @return array<string, string|int|float>
     */
    protected function transformationParameters(Conversion $conversion): array
    {
        $parameters = $this->cloudflareImage($conversion)->toParameters();

        if ($parameters === []) {
            throw CloudflareTransformationFailed::noParameters($conversion->getName());
        }

        return $parameters;
    }

    /**
     * @param  array<string, string|int|float>  $parameters
     */
    protected function transformationUrlForParameters(array $parameters, string $sourceUrl): string
    {
        $zone = rtrim((string) ($this->config['zone'] ?? ''), '/');

        if ($zone === '') {
            throw CloudflareTransformationFailed::missingZone();
        }

        $parameterString = collect($parameters)
            ->map(fn (string|int|float $value, string $name) => "{$name}={$value}")
            ->implode(',');

        return "{$zone}/cdn-cgi/image/{$parameterString}/{$sourceUrl}";
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
