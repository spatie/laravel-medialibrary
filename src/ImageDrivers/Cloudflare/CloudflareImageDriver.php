<?php

namespace Spatie\MediaLibrary\ImageDrivers\Cloudflare;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\GeneratesConversionFiles;
use Spatie\MediaLibrary\ImageDrivers\ProvidesConversionExtension;
use Spatie\MediaLibrary\MediaCollections\Exceptions\CloudflareTransformationFailed;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

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

        if ($this->originalIsPrivate($media)) {
            throw CloudflareTransformationFailed::privateDisk($media->disk);
        }

        $url = $this->transformationUrl($media, $conversion);

        // Cloudflare negotiates the output format from the Accept header, so we
        // ask for the format this conversion is stored as. Otherwise a webp
        // conversion could come back as the original format on a server side
        // fetch, and the stored file would not match its extension.
        // Sinking straight to the temporary file keeps a large transformation out
        // of the worker's memory.
        $response = Http::withHeaders(['Accept' => $this->acceptHeader($media, $conversion)])
            ->timeout($this->config['timeout'] ?? 30)
            ->sink($file)
            ->get($url);

        if ($response->failed()) {
            throw CloudflareTransformationFailed::requestFailed($url, $response->status());
        }

        return $file;
    }

    public function conversionExtension(Conversion $conversion, string $originalExtension): string
    {
        return $this->targetFormat($conversion)?->extension() ?? $originalExtension;
    }

    /**
     * Cloudflare fetches the original over the public internet, so a private
     * original can never be transformed. We can only warn about the cases we can
     * positively determine, and leave anything uncertain to the request itself.
     */
    protected function originalIsPrivate(Media $media): bool
    {
        try {
            $visibility = Storage::disk($media->disk)->getVisibility($media->getPathRelativeToRoot());
        } catch (Throwable) {
            return false;
        }

        return $visibility === 'private';
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
