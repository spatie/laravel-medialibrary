<?php

namespace Spatie\MediaLibrary\ResponsiveImages;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Helpers\TemporaryDirectory;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator;
use Spatie\Image\Image;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryFactory;

class ResponsiveImageGenerator
{
    /** \Spatie\MediaLibrary\Filesystem\Filesystem */
    protected $filesystem;

    /** \Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\WidthCalculator */
    protected $widthCalculator;

    public function __construct(
        Filesystem $filesystem,
        WidthCalculator $widthCalculator
    ) {
        $this->filesystem = $filesystem;

        $this->widthCalculator = $widthCalculator;
    }

    public function generateResponsiveImages(Media $media)
    {
        $temporaryDirectory = TemporaryDirectory::create();
        
        $baseImage = app(Filesystem::class)->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(str_random(16).'.'.$media->extension)
        );

        foreach ($this->widthCalculator->calculateWidths($baseImage) as $width) {
            $this->generateResponsiveImage($media, $baseImage, $width, $temporaryDirectory);
        }

        $temporaryDirectory->delete();
    }

    public function generateResponsiveImage(Media $media, string $baseImage, int $targetWidth, BaseTemporaryFactory $temporaryDirectory)
    {
        $responsiveImageFileName = $this->appendToFileName($media->file_name, "medialibrary_original_{$targetWidth}");
   
        $tempDestination = $temporaryDirectory->path($responsiveImageFileName);

        Image::load($baseImage)->width($targetWidth)->save($tempDestination);

        $this->filesystem->copyToMediaLibrary($tempDestination, $media, 'responsiveImages');
    }

    protected function appendToFileName(string $filePath, string $suffix): string
    {
        $baseName = pathinfo($filePath, PATHINFO_FILENAME);

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
  
        return $baseName . '_' . $suffix . '.' . $extension;
    }
}
