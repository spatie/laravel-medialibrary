<?php

namespace Spatie\MediaLibrary\ImageDrivers;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Exceptions\UnsupportedImageFormat;
use Spatie\Image\Image;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SpatieImageDriver implements GeneratesConversionFiles, SupportsResponsiveImages
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {}

    public function imageClass(): string
    {
        return ImageDriver::class;
    }

    public function convert(Media $media, Conversion $conversion, string $file): string
    {
        $image = Image::useImageDriver($this->config['engine'] ?? 'gd')
            ->loadFile($file)
            ->format('jpg');

        try {
            if ($closure = $conversion->getManipulationClosure()) {
                ($closure->getClosure())($image);
            }

            $conversion->getManipulations()->apply($image);

            $image->save();
        } catch (UnsupportedImageFormat) {

        }

        return $file;
    }
}
