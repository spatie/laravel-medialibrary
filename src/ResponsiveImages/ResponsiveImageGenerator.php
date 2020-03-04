<?php

namespace Spatie\Medialibrary\ResponsiveImages;

use Illuminate\Support\Str;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\Events\ResponsiveImagesGenerated;
use Spatie\Medialibrary\Filesystem\Filesystem;
use Spatie\Medialibrary\Support\File;
use Spatie\Medialibrary\Support\ImageFactory;
use Spatie\Medialibrary\Support\TemporaryDirectory;
use Spatie\Medialibrary\Models\Media;
use Spatie\Medialibrary\ResponsiveImages\Exceptions\InvalidTinyJpg;
use Spatie\Medialibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Spatie\Medialibrary\ResponsiveImages\WidthCalculator\WidthCalculator;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class ResponsiveImageGenerator
{
    /** \Spatie\Medialibrary\Filesystem\Filesystem */
    protected \Spatie\Medialibrary\Filesystem\Filesystem $filesystem;

    /** \Spatie\Medialibrary\ResponsiveImages\WidthCalculator\WidthCalculator */
    protected \Spatie\Medialibrary\ResponsiveImages\WidthCalculator\WidthCalculator $widthCalculator;

    /** \Spatie\Medialibrary\ResponsiveImages\TinyPlaceHolderGenerator\TinyPlaceHolderGenerator */
    protected \Spatie\Medialibrary\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator $tinyPlaceholderGenerator;

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

        $baseImage = app(Filesystem::class)->copyFromMedialibrary(
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

        $this->filesystem->copyToMedialibrary($finalResponsiveImagePath, $media, 'responsiveImages');

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
