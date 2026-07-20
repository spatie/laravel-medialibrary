<?php

namespace Spatie\MediaLibrary\Conversions\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ImageDrivers\GeneratesConversionFiles;
use Spatie\MediaLibrary\ImageDrivers\ImageDriverManager;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidImageDriver;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PerformManipulationsAction
{
    public function execute(
        Media $media,
        Conversion $conversion,
        string $imageFile,
    ): string {

        if ($conversion->getManipulations()->isEmpty() && ! $conversion->getManipulationClosure()) {
            return $imageFile;
        }

        if (! File::exists($imageFile)) {
            return '';
        }

        $conversionTempFile = $this->getConversionTempFileName($media, $conversion, $imageFile);

        File::copy($imageFile, $conversionTempFile);

        $supportedFormats = ['jpg', 'jpeg', 'pjpg', 'png', 'gif', 'webp'];
        if ($conversion->shouldKeepOriginalImageFormat() && in_array($media->extension, $supportedFormats)) {
            $conversion->format($media->extension);
        }

        $driver = app(ImageDriverManager::class)->forConversion($conversion);

        if (! $driver instanceof GeneratesConversionFiles) {
            throw InvalidImageDriver::cannotGenerateFiles($driver::class);
        }

        return $driver->convert($media, $conversion, $conversionTempFile);
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

        $fileName = Str::random(32)."{$conversion->getName()}.{$extension}";

        return "{$directory}/{$fileName}";
    }
}
