<?php

namespace Spatie\MediaLibrary\Conversions\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Image\Exceptions\UnsupportedImageFormat;
use Spatie\Image\Image;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\Manipulations;
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

        if (! File::exists($imageFile)) {
            return '';
        }

        $conversionTempFile = $this->getConversionTempFileName($media, $conversion, $imageFile);

        File::copy($imageFile, $conversionTempFile);

        $supportedFormats = ['jpg', 'jpeg', 'pjpg', 'png', 'gif', 'webp', 'avif'];
        if ($conversion->shouldKeepOriginalImageFormat() && in_array($media->extension, $supportedFormats)) {
            $conversion->format($media->extension);
        }

        $this->applyFocalPoint($media, $conversion);

        $image = Image::useImageDriver(config('media-library.image_driver'))
            ->loadFile($conversionTempFile)
            ->format('jpg');

        try {
            $conversion->getManipulations()->apply($image);

            $image->save();
        } catch (UnsupportedImageFormat) {

        }

        return $conversionTempFile;
    }

    protected function applyFocalPoint(Media $media, Conversion $conversion): void
    {
        if (! $conversion->shouldUseFocalPoint()) {
            return;
        }

        if (! $media->hasFocalPoint()) {
            return;
        }

        $manipulationArray = $conversion->getManipulations()->toArray();

        [$width, $height] = $this->extractTargetDimensions($manipulationArray);

        if (! $width || ! $height) {
            return;
        }

        $focalPoint = $media->getFocalPoint();

        $conversion->removeManipulation('width');
        $conversion->removeManipulation('height');
        $conversion->removeManipulation('crop');
        $conversion->removeManipulation('fit');

        $focalCropManipulations = new Manipulations([
            'focalCropAndResize' => [
                $width,
                $height,
                (int) round($focalPoint['x']),
                (int) round($focalPoint['y']),
            ],
        ]);

        $conversion->addAsFirstManipulations($focalCropManipulations);
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    protected function extractTargetDimensions(array $manipulationArray): array
    {
        if (isset($manipulationArray['crop'])) {
            return [
                (int) ($manipulationArray['crop'][0] ?? null),
                (int) ($manipulationArray['crop'][1] ?? null),
            ];
        }

        if (isset($manipulationArray['fit'])) {
            return [
                (int) ($manipulationArray['fit'][1] ?? $manipulationArray['fit']['desiredWidth'] ?? null),
                (int) ($manipulationArray['fit'][2] ?? $manipulationArray['fit']['desiredHeight'] ?? null),
            ];
        }

        $width = isset($manipulationArray['width'])
            ? (int) ($manipulationArray['width'][0] ?? null)
            : null;

        $height = isset($manipulationArray['height'])
            ? (int) ($manipulationArray['height'][0] ?? null)
            : null;

        return [$width, $height];
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
