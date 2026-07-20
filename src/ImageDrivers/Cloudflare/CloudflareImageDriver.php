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

        // Cloudflare negotiates the output format from the Accept header, so we
        // ask for the format this conversion is stored as. Otherwise a webp
        // conversion could come back as the original format on a server side
        // fetch, and the stored file would not match its extension.
        $response = Http::withHeaders(['Accept' => $this->acceptHeader($media, $conversion)])
            ->timeout($this->config['timeout'] ?? 30)
            ->get($url);

        if ($response->failed()) {
            throw CloudflareTransformationFailed::requestFailed($url, $response->status());
        }

        file_put_contents($file, $response->body());

        return $file;
    }

    public function conversionExtension(Conversion $conversion, string $originalExtension): string
    {
        return $this->targetFormat($conversion)?->extension() ?? $originalExtension;
    }

    protected function acceptHeader(Media $media, Conversion $conversion): string
    {
        return $this->targetFormat($conversion)?->mimeType()
            ?? ($media->mime_type ?: 'image/*');
    }

    /**
     * The concrete format this conversion is stored as, or null when it keeps
     * the original format (the `auto` and unset cases).
     */
    protected function targetFormat(Conversion $conversion): ?CloudflareFormat
    {
        $format = $this->cloudflareImage($conversion)->toParameters()['format'] ?? null;

        $format = $format ? CloudflareFormat::tryFrom((string) $format) : null;

        return $format === CloudflareFormat::Auto ? null : $format;
    }
}
