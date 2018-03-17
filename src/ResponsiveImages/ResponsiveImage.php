<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

class ResponsiveImage
{
    /** @var string */
    public $fileName = '';

    /** @var \Spatie\MediaLibrary\Models\Media */
    public $media;

    public static function register(Media $media, $fileName, $conversionName)
    {
        $responsiveImages = $media->responsive_images;

        $responsiveImages[$conversionName]['urls'][] = $fileName;

        $media->responsive_images = $responsiveImages;

        $media->save();
    }

    public static function registerTinySvg(Media $media, string $base64Svg, string $conversionName)
    {
        $responsiveImages = $media->responsive_images;

        $responsiveImages[$conversionName]['base64svg'] = $base64Svg;

        $media->responsive_images = $responsiveImages;

        $media->save();
    }

    public function __construct(string $fileName, Media $media)
    {
        $this->fileName = $fileName;

        $this->media = $media;
    }

    public function url(): string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this->media);

        return $urlGenerator->getResponsiveImagesDirectoryUrl().$this->fileName;
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
        $between = strstr($subject, $startCharacter);

        $between = str_replace('___', '', $between);

        $between = strstr($between, $endCharacter, true);

        return $between;
    }

    public function delete()
    {
        $pathGenerator = PathGeneratorFactory::create();

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
