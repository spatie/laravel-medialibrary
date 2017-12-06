<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\UrlGenerator\UrlGeneratorFactory;

class ResponsiveImage
{
    /** @var string */
    public $fileName = '';

    /** @var \Spatie\MediaLibrary\Media */
    protected $media;

    public function __construct(string $fileName, Media $media)
    {
        $this->fileName = $fileName;

        $this->media = $media;
    }

    public function generatedFor(): string
    {
        $originalFileName = pathinfo($this->media->file_name, PATHINFO_FILENAME);

        $shortenedFileName = str_replace_first($originalFileName . '_', '', $this->fileName);

        return $this->stringBefore($shortenedFileName, '_');
    }

    public function url(): string
    {
        $urlGenerator = UrlGeneratorFactory::createForMedia($this->media);

        return $urlGenerator->getResponsiveImagesDirectoryUrl() . $this->fileName;
    }

    public function width(): int
    {
        $originalFileName = pathinfo($this->media->file_name, PATHINFO_FILENAME);
        
        $shortenedFileName = str_replace_first($originalFileName . '_', '', $this->fileName);

        $shortenedFileName = str_replace($this->generatedFor() . '_', '', $shortenedFileName);

        return (int)$this->stringBetween($shortenedFileName, '_', '.');
    }

    public static function register(Media $media, $fileName)
    {
        $responsiveImages = $media->responsive_images ?? [];

        $responsiveImages[] = $fileName;

        $media->responsive_images = $responsiveImages;
    
        $media->save();
    }

    protected function stringBefore(string $subject, string $needle)
    {
        return substr($subject, 0, strrpos($subject, $needle));
    }

    protected function stringBetween(string $subject, string $startCharacter, string $endCharacter): string
    {
        $between = strstr($subject, $startCharacter);

        $between = strstr($subject, $endCharacter, true);

        return $between;
    }
}
