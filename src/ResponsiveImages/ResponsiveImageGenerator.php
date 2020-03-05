<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\ResponsiveImages\Events\ResponsiveImagesGenerated;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\Support\ImageFactory;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Exceptions\InvalidTinyJpg;
use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class ResponsiveImageGenerator
{
    protected Filesystem $filesystem;

    protected WidthCalculator $widthCalculator;

    protected TinyPlaceholderGenerator $tinyPlaceholderGenerator;

    public function __construct(
        Filesystem $filesystem,
        WidthCalculator $widthCalculator,
        TinyPlaceholderGenerator $tinyPlaceholderGenerator
    ) {
        $this->filesystem = $filesystem;

        $this->widthCalculator = $widthCalculator;

        $this->tinyPlaceholderGenerator = $tinyPlaceholderGenerator;
    }

    public function generateResponsiveImages(Media $media)
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

    public function generateResponsiveImagesForConversion(Media $media, Conversion $conversion, string $baseImage)
    {
        $temporaryDirectory = TemporaryDirectory::create();

        $media = $this->cleanResponsiveImages($media, $conversion->getName());

        foreach ($this->widthCalculator->calculateWidthsFromFile($baseImage) as $width) {
            $this->generateResponsiveImage($media, $baseImage, $conversion->getName(), $width, $temporaryDirectory);
        }

        $this->generateTinyJpg($media, $baseImage, $conversion->getName(), $temporaryDirectory);

        $temporaryDirectory->delete();
    }

    public function generateResponsiveImage(
        Media $media,
        string $baseImage,
        string $conversionName,
        int $targetWidth,
        BaseTemporaryDirectory $temporaryDirectory
    ) {
        $responsiveImagePath = $this->appendToFileName($media->file_name, "___{$conversionName}_{$targetWidth}");

        $tempDestination = $temporaryDirectory->path($responsiveImagePath);

        ImageFactory::load($baseImage)
            ->optimize()
            ->width($targetWidth)
            ->save($tempDestination);

        $responsiveImageHeight = ImageFactory::load($tempDestination)->getHeight();

        $finalImageFileName = $this->appendToFileName($responsiveImagePath, "_{$responsiveImageHeight}");

        $finalResponsiveImagePath = $temporaryDirectory->path($finalImageFileName);

        rename($tempDestination, $finalResponsiveImagePath);

        $this->filesystem->copyToMediaLibrary($finalResponsiveImagePath, $media, 'responsiveImages');

        ResponsiveImage::register($media, $finalImageFileName, $conversionName);
    }

    public function generateTinyJpg(Media $media, string $originalImagePath, string $conversionName, BaseTemporaryDirectory $temporaryDirectory)
    {
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

    protected function appendToFileName(string $filePath, string $suffix): string
    {
        $baseName = pathinfo($filePath, PATHINFO_FILENAME);

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return $baseName.$suffix.'.'.$extension;
    }

    protected function guardAgainstInvalidTinyPlaceHolder(string $tinyPlaceholderPath)
    {
        if (! file_exists($tinyPlaceholderPath)) {
            throw InvalidTinyJpg::doesNotExist($tinyPlaceholderPath);
        }

        $mimeType = File::getMimeType($tinyPlaceholderPath);

        if ($mimeType !== 'image/jpeg') {
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
}
