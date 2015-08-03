<?php

namespace Spatie\MediaLibrary;

use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;
use Spatie\MediaLibrary\Helpers\Gitignore;
use Spatie\MediaLibrary\Jobs\PerformConversions;
use Spatie\PdfToImage\Pdf;

class FileManipulator
{
    use DispatchesJobs;

    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function createDerivedFiles(Media $media)
    {
        if ($media->type == Media::TYPE_OTHER) {
            return;
        }

        if ($media->type == Media::TYPE_PDF && !class_exists('Imagick')) {
            return;
        }

        $profileCollection = ConversionCollectionFactory::createForMedia($media);

        $this->performConversions($profileCollection->getNonQueuedConversions($media->collection_name), $media);

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if (count($queuedConversions)) {
            $this->dispatch(new PerformConversions($queuedConversions, $media));
        }
    }

    /**
     * Perform the given conversions for the given media.
     *
     * @param \Spatie\MediaLibrary\Conversion\ConversionCollection $conversions
     * @param \Spatie\MediaLibrary\Media                           $media
     */
    public function performConversions(ConversionCollection $conversions, Media $media)
    {
        $tempDirectory = $this->createTempDirectory();

        $copiedOriginalFile = $tempDirectory.'/'.str_random(16).'.'.$media->extension;

        app(Filesystem::class)->copyFromMediaLibrary($media, $copiedOriginalFile);

        if ($media->type == Media::TYPE_PDF) {
            $copiedOriginalFile = $this->convertToImage($copiedOriginalFile);
        }

        foreach ($conversions as $conversion) {
            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            $renamedFile = MediaLibraryFileHelper::renameInDirectory($conversionResult, $conversion->getName().'.'.
                $conversion->getResultExtension(pathinfo($copiedOriginalFile, PATHINFO_EXTENSION)));

            app(Filesystem::class)->copyToMediaLibrary($renamedFile, $media, 'conversions');
        }

        File::deleteDirectory($tempDirectory);
    }

    /**
     * Perform the conversion.
     *
     * @param \Spatie\MediaLibrary\Media $media
     * @param Conversion                 $conversion
     * @param string                     $copiedOriginalFile
     *
     * @return string
     */
    public function performConversion(Media $media, Conversion $conversion, $copiedOriginalFile)
    {
        $conversionTempFile = pathinfo($copiedOriginalFile, PATHINFO_DIRNAME).'/'.string()->random(16).
            $conversion->getName().'.'.$media->extension;

        File::copy($copiedOriginalFile, $conversionTempFile);

        foreach ($conversion->getManipulations() as $manipulation) {
            (new GlideImage())
                ->load($conversionTempFile, $manipulation)
                ->useAbsoluteSourceFilePath()
                ->save($conversionTempFile);
        }

        return $conversionTempFile;
    }

    /**
     * Create a directory to store some working files.
     *
     * @return string
     */
    public function createTempDirectory()
    {
        $tempDirectory = storage_path('medialibrary/temp/'.str_random(16));

        File::makeDirectory($tempDirectory, 493, true);

        Gitignore::createIn(storage_path('medialibrary'));

        return $tempDirectory;
    }

    /**
     * @param string $pdfFile
     *
     * @return string
     */
    protected function convertToImage($pdfFile)
    {
        $imageFile = string($pdfFile)->pop('.').'.jpg';

        (new Pdf($pdfFile))->saveImage($imageFile);

        return $imageFile;
    }
}
