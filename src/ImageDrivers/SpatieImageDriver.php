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
        $image = Image::useImageDriver($this->config['engine'] ?? 'gd')->loadFile($file);

        try {
            $conversion->getManipulations()->apply($image);

            // The closure runs last so it can refine what the conversion's own
            // manipulations set up.
            if ($closure = $conversion->getManipulationClosure()) {
                ($closure->getClosure())($image);
            }

            // The conversion file is named before it is generated, so the format
            // that name was derived from has the final say. Set the output format
            // with format() on the conversion, not inside the closure.
            $image->format($conversion->getResultExtension($media->extension) ?: 'jpg');

            $image->save();
        } catch (UnsupportedImageFormat) {

        }

        return $file;
    }
}
