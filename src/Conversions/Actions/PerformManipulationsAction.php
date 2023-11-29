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

        $conversionTempFile = $this->getConversionTempFileName($media, $conversion, $imageFile);

        File::copy($imageFile, $conversionTempFile);

        $supportedFormats = ['jpg', 'pjpg', 'png', 'gif'];
        if ($conversion->shouldKeepOriginalImageFormat() && in_array($media->extension, $supportedFormats)) {
            $conversion->format($media->extension);
        }

        $image = Image::useImageDriver(config('media-library.image_driver'))
            ->load($conversionTempFile);

        $conversion->getManipulations()->apply($image);

        $image->save();

        return $conversionTempFile;
    }

    protected function getConversionTempFileName(
        Media $media,
        Conversion $conversion,
        string $imageFile,
    ): string {
        $directory = pathinfo($imageFile, PATHINFO_DIRNAME);

        $fileName = Str::random(32)."{$conversion->getName()}.{$media->extension}";

        return "{$directory}/{$fileName}";
    }
}
