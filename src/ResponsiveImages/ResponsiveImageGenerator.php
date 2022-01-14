<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGenerated;
use Spatie\MediaLibrary\ResponsiveImages\Exceptions\InvalidTinyJpg;
use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;
use Spatie\MediaLibrary\Support\ImageFactory;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class ResponsiveImageGenerator
{
    protected const DEFAULT_CONVERSION_QUALITY = 90;

    protected FileNamer $fileNamer;

    public function __construct(
        protected Filesystem $filesystem,
        protected WidthCalculator $widthCalculator,
        protected TinyPlaceholderGenerator $tinyPlaceholderGenerator
    ) {
        $this->fileNamer = app(config('media-library.file_namer'));
    }

    public function generateResponsiveImages(Media $media): void
    {
        $temporaryDirectory = TemporaryDirectory::create();

        $baseImage = app(Filesystem::class)->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(Str::random(16).'.'.$media->extension)
        );

        $media = $this->cleanResponsiveImages($media);

        foreach ($this->widthCalculator->calculateWidthsFromFile($baseImage) as $width) {
            $this->generateResponsiveImage($media, $baseImage, 'media_library_original', $width, $temporaryDirectory);
        }

        event(new ResponsiveImagesGenerated($media));

        $this->generateTinyJpg($media, $baseImage, 'media_library_original', $temporaryDirectory);

        $temporaryDirectory->delete();
    }

    public function generateResponsiveImagesForConversion(Media $media, Conversion $conversion, string $baseImage): void
    {
        $temporaryDirectory = TemporaryDirectory::create();

        $media = $this->cleanResponsiveImages($media, $conversion->getName());

        foreach ($this->widthCalculator->calculateWidthsFromFile($baseImage) as $width) {
            $this->generateResponsiveImage($media, $baseImage, $conversion->getName(), $width, $temporaryDirectory, $this->getConversionQuality($conversion));
        }

        $this->generateTinyJpg($media, $baseImage, $conversion->getName(), $temporaryDirectory);

        $temporaryDirectory->delete();
    }

    private function getConversionQuality(Conversion $conversion): int
    {
        return $conversion->getManipulations()->getManipulationArgument('quality') ?: self::DEFAULT_CONVERSION_QUALITY;
    }

    public function generateResponsiveImage(
        Media $media,
        string $baseImage,
        string $conversionName,
        int $targetWidth,
        BaseTemporaryDirectory $temporaryDirectory,
        int $conversionQuality = self::DEFAULT_CONVERSION_QUALITY
    ): void {
        $extension = $this->fileNamer->extensionFromBaseImage($baseImage);
        $responsiveImagePath = $this->fileNamer->temporaryFileName($media, $extension);

        $tempDestination = $temporaryDirectory->path($responsiveImagePath);

        ImageFactory::load($baseImage)
            ->optimize()
            ->width($targetWidth)
            ->quality($conversionQuality)
            ->save($tempDestination);

        $responsiveImageHeight = ImageFactory::load($tempDestination)->getHeight();

        // Users can customize the name like they want, but we expect the last part in a certain format
        $fileName = $this->addPropertiesToFileName(
            $responsiveImagePath,
            $conversionName,
            $targetWidth,
            $responsiveImageHeight,
            $extension
        );

        $responsiveImagePath = $temporaryDirectory->path($fileName);

        rename($tempDestination, $responsiveImagePath);

        $this->filesystem->copyToMediaLibrary($responsiveImagePath, $media, 'responsiveImages');

        ResponsiveImage::register($media, $fileName, $conversionName);
    }

    public function generateTinyJpg(
        Media $media,
        string $originalImagePath,
        string $conversionName,
        BaseTemporaryDirectory $temporaryDirectory
    ): void {
        $tempDestination = $temporaryDirectory->path('tiny.jpg');

        $this->tinyPlaceholderGenerator->generateTinyPlaceholder($originalImagePath, $tempDestination);

        $this->guardAgainstInvalidTinyPlaceHolder($tempDestination);

        $tinyImageDataBase64 = base64_encode(file_get_contents($tempDestination));

        $tinyImageBase64 = 'data:image/jpeg;base64,'.$tinyImageDataBase64;

        $originalImage = ImageFactory::load($originalImagePath);

        $originalImageWidth = $originalImage->getWidth();

        $originalImageHeight = $originalImage->getHeight();

        $svg = view('media-library::placeholderSvg', compact(
            'originalImageWidth',
            'originalImageHeight',
            'tinyImageBase64'
        ));

        $base64Svg = 'data:image/svg+xml;base64,'.base64_encode($svg);

        ResponsiveImage::registerTinySvg($media, $base64Svg, $conversionName);
    }

    protected function appendToFileName(string $filePath, string $suffix, string $extensionFilePath = null): string
    {
        $baseName = pathinfo($filePath, PATHINFO_FILENAME);

        $extension = pathinfo($extensionFilePath ?? $filePath, PATHINFO_EXTENSION);

        return "{$baseName}{$suffix}.{$extension}";
    }

    protected function guardAgainstInvalidTinyPlaceHolder(string $tinyPlaceholderPath): void
    {
        if (! file_exists($tinyPlaceholderPath)) {
            throw InvalidTinyJpg::doesNotExist($tinyPlaceholderPath);
        }

        if (File::getMimeType($tinyPlaceholderPath) !== 'image/jpeg') {
            throw InvalidTinyJpg::hasWrongMimeType($tinyPlaceholderPath);
        }
    }

    protected function cleanResponsiveImages(Media $media, string $conversionName = 'media_library_original'): Media
    {
        $responsiveImages = $media->responsive_images;
        $responsiveImages[$conversionName]['urls'] = [];
        $media->responsive_images = $responsiveImages;

        $this->filesystem->removeResponsiveImages($media, $conversionName);

        return $media;
    }

    protected function addPropertiesToFileName(string $fileName, string $conversionName, int $width, int $height, string $extension): string
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        return "{$fileName}___{$conversionName}_{$width}_{$height}.{$extension}";
    }
}
