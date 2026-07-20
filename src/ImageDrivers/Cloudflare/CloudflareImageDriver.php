<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\GeneratesConversionFiles;
use Spatie\MediaLibrary\ImageDrivers\ProvidesConversionExtension;
use Spatie\MediaLibrary\MediaCollections\Exceptions\CloudflareTransformationFailed;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CloudflareImageDriver implements GeneratesConversionFiles, ProvidesConversionExtension
{
    use BuildsTransformationUrls;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {}

    public function convert(Media $media, Conversion $conversion, string $file): string
    {
        if ($media->type !== 'image') {
            throw CloudflareTransformationFailed::unsupportedMediaType($media->file_name);
        }

        $url = $this->transformationUrl($media, $conversion);

        $response = Http::timeout($this->config['timeout'] ?? 30)->get($url);

        if ($response->failed()) {
            throw CloudflareTransformationFailed::requestFailed($url, $response->status());
        }

        file_put_contents($file, $response->body());

        return $file;
    }

    public function conversionExtension(Conversion $conversion, string $originalExtension): string
    {
        $format = $this->cloudflareImage($conversion)->toParameters()['format'] ?? null;

        return ($format ? CloudflareFormat::tryFrom((string) $format)?->extension() : null)
            ?? $originalExtension;
    }
}
