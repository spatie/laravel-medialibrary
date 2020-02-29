<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Events\ResponsiveImagesGenerated;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Helpers\ImageFactory;
use Spatie\MediaLibrary\Helpers\TemporaryDirectory;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Exceptions\InvalidTinyJpg;
use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class ResponsiveImageGenerator
{
    /** \Spatie\MediaLibrary\Filesystem\Filesystem */
    protected $filesystem;

    /** \Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator */
    protected $widthCalculator;

    /** \Spatie\MediaLibrary\ResponsiveImages\TinyPlaceHolderGenerator\TinyPlaceHolderGenerator */
    protected $tinyPlaceholderGenerator;

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
            $this->generateResponsiveImage($media, $baseImage, 'medialibrary_original', $width, $temporaryDirectory);
        }

        event(new ResponsiveImagesGenerated($media));

        $this->generateTinyJpg($media, $baseImage, 'medialibrary_original', $temporaryDirectory);

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
        $responsiveImagePath = $this->appendToFileName($media->file_name, "___{$conversionName}_{$targetWidth}", $baseImage);

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

        $svg = view('medialibrary::placeholderSvg', compact(
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

        return $baseName.$suffix.'.'.$extension;
    }

    protected function guardAgainstInvalidTinyPlaceHolder(string $tinyPlaceholderPath)
    {
        if (! file_exists($tinyPlaceholderPath)) {
            throw InvalidTinyJpg::doesNotExist($tinyPlaceholderPath);
        }

        $mimeType = File::getMimetype($tinyPlaceholderPath);

        if ($mimeType !== 'image/jpeg') {
            throw InvalidTinyJpg::hasWrongMimeType($tinyPlaceholderPath);
        }
    }

    protected function cleanResponsiveImages(Media $media, string $conversionName = 'medialibrary_original'): Media
    {
        $responsiveImages = $media->responsive_images;
        $responsiveImages[$conversionName]['urls'] = [];
        $media->responsive_images = $responsiveImages;

        $this->filesystem->removeResponsiveImages($media, $conversionName);

        return $media;
    }
}
