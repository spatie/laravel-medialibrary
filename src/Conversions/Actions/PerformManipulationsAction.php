<?php

namespace Spatie\MediaLibrary\Conversions\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PerformManipulationsAction
{
    public function execute(
        Media $media,
        Conversion $conversion,
        string $imageFile,
    ): string {

        if ($conversion->getManipulations()->isEmpty()) {
            return $imageFile;
        }

        $supportedFormats = ['jpg', 'pjpg', 'png', 'gif'];
        if ($conversion->shouldKeepOriginalImageFormat() && in_array($media->extension, $supportedFormats)) {
            $conversion->format($media->extension);
        }

        $conversionTempFile = $this->getConversionTempFileName($media, $conversion, $imageFile);

        $image = Image::useImageDriver(config('media-library.image_driver'))
            ->loadFile($imageFile)
            ->format('jpg');

        $conversion->getManipulations()->apply($image);

        $image->save($conversionTempFile);

        return $conversionTempFile;
    }

    protected function getConversionTempFileName(
        Media $media,
        Conversion $conversion,
        string $imageFile,
    ): string {
        $directory = pathinfo($imageFile, PATHINFO_DIRNAME);

        $extension = $media->extension;

        if ($extension === '') {
            $extension = 'jpg';
        }

        $format = $conversion->getManipulations()->getFirstManipulationArgument('format');
        if($format !== null) {
            $extension = $format;
        }

        $fileName = Str::random(32)."{$conversion->getName()}.{$extension}";

        return "{$directory}/{$fileName}";
    }
}
