<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Models\Media;

class RegisteredResponsiveImages
{
    /** Spatie\Medialibrary\Media */
    protected $media;

    /** Illuminate\Support\Collection */
    public $files;

    /** string */
    public $generatedFor;

    public function __construct(Media $media, string $conversionName = '')
    {
        $this->media = $media;

        $this->generatedFor = $conversionName === ''
            ? 'medialibrary_original'
            : $conversionName;

        $this->files = collect($media->responsive_images[$this->generatedFor]['urls'] ?? [])
            ->map(function (string $fileName) use ($media) {
                return new ResponsiveImage($fileName, $media);
            })
            ->filter(function (ResponsiveImage $responsiveImage) {
                return $responsiveImage->generatedFor() === $this->generatedFor;
            });
    }

    public function getUrls(): array
    {
        return $this->files
            ->map(function (ResponsiveImage $responsiveImage) {
                return $responsiveImage->url();
            })
            ->values()
            ->toArray();
    }

    public function getSrcset(): string
    {
        $filesSrcset = $this->files
            ->map(function (ResponsiveImage $responsiveImage) {
                return "{$responsiveImage->url()} {$responsiveImage->width()}w";
            })
            ->implode(', ');

        $shouldAddPlaceholderSvg = config('medialibrary.responsive_images.use_tiny_placeholders')
            && $this->getPlaceholderSvg();

        if ($shouldAddPlaceholderSvg) {
            $filesSrcset .= ', '.$this->getPlaceholderSvg().' 32w';
        }

        return $filesSrcset;
    }

    public function getPlaceholderSvg(): ?string
    {
        return $this->media->responsive_images[$this->generatedFor]['base64svg'] ?? null;
    }

    public function delete()
    {
        $this->files->each->delete();

        $responsiveImages = $this->media->responsive_images;

        unset($responsiveImages[$this->generatedFor]);

        $this->media->responsive_images = $responsiveImages;

        $this->media->save();
    }
}
