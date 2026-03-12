<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGeneratorFactory;

class ResponsiveImage
{
    public static function register(Media $media, $fileName, $conversionName): void
    {
        $responsiveImages = $media->responsive_images;

        $responsiveImages[$conversionName]['urls'][] = $fileName;

        $media->responsive_images = $responsiveImages;

        $media->save();
    }

    public static function registerTinySvg(Media $media, string $base64Svg, string $conversionName): void
    {
        $responsiveImages = $media->responsive_images;

        $responsiveImages[$conversionName]['base64svg'] = $base64Svg;

        $media->responsive_images = $responsiveImages;

        $media->save();
    }

    public function __construct(public string $fileName, public Media $media) {}

    public function url(): string
    {
        $conversionName = '';

        if ($this->generatedFor() !== 'media_library_original') {
            $conversionName = $this->generatedFor();
        }

        $urlGenerator = UrlGeneratorFactory::createForMedia($this->media, $conversionName);

        $baseUrl = $urlGenerator->getResponsiveImagesDirectoryUrl();
        $encodedFileName = rawurlencode($this->fileName);

        if (str_contains($baseUrl, '?')) {
            $url = Str::before($baseUrl, '?').$encodedFileName.'?'.Str::after($baseUrl, '?');
        } else {
            $url = $baseUrl.$encodedFileName;
        }

        if (config('media-library.version_urls') === true) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url = "{$url}{$separator}v={$this->media->updated_at->timestamp}";
        }

        return $url;
    }

    public function generatedFor(): string
    {
        $propertyParts = $this->getPropertyParts();

        array_pop($propertyParts);

        array_pop($propertyParts);

        return implode('_', $propertyParts);
    }

    public function width(): int
    {
        $propertyParts = $this->getPropertyParts();

        array_pop($propertyParts);

        return (int) last($propertyParts);
    }

    public function height(): int
    {
        $propertyParts = $this->getPropertyParts();

        return (int) last($propertyParts);
    }

    protected function getPropertyParts(): array
    {
        $propertyString = $this->stringBetween($this->fileName, '___', '.');

        return explode('_', $propertyString);
    }

    protected function stringBetween(string $subject, string $startCharacter, string $endCharacter): string
    {
        $lastPos = strrpos($subject, $startCharacter);

        $between = substr($subject, $lastPos);

        $between = str_replace('___', '', $between);

        $between = strstr($between, $endCharacter, true);

        return $between;
    }

    /**
     * @return $this
     */
    public function delete(): self
    {
        $pathGenerator = PathGeneratorFactory::create($this->media);

        $path = $pathGenerator->getPathForResponsiveImages($this->media);

        $fullPath = $path.$this->fileName;

        app(Filesystem::class)->removeFile($this->media, $fullPath);

        $responsiveImages = $this->media->responsive_images;

        unset($responsiveImages[$this->generatedFor()]);

        $this->media->responsive_images = $responsiveImages;

        $this->media->save();

        return $this;
    }
}
