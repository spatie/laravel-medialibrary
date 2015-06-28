<?php

namespace Spatie\MediaLibrary;

use File;
use GlideImage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;

class MediaLibraryFileManipulator
{
    use DispatchesJobs;

    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ConversionCollectionFactory::createForMedia($media);

        $this->performConversions($profileCollection->getNonQueuedConversions($media->collection_name), $media);

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if (count($queuedConversions)) {
            $this->dispatch(new PerformConversions($queuedConversions, $media));
        }
    }

    public function performConversions($conversions, $media)
    {
        $tempDirectory = storage_path('media-library/temp/' . string()->random(16));

        $copiedOriginalFile = storage_path('media-library/temp/' . string()->random(16) . '.' . pathinfo($media->file), PATHINFO_EXTENSION);

        app(MediaLibraryFileSystem::class)->copyOriginalFile($media, $copiedOriginalFile);

        foreach ($conversions as $conversion) {

            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            app(MediaLibraryFileSystem::class)->copyFileToMediaLibraryForMedia($conversionResult, $media);

        }

        File::delete($tempDirectory);
    }

    /**
     * @param $media
     * @param $conversion
     * @param $copiedOriginalFile
     * @return string
     */
    public function performConversion($media, $conversion, $copiedOriginalFile)
    {
        $conversionTempFile = storage_path('media-library/temp/' . string()->random(16) . $conversion->getName() . '.' . pathinfo($media->file), PATHINFO_EXTENSION);

        File::copy($copiedOriginalFile, $conversionTempFile);

        foreach ($conversion->getManipulations() as $manipulation) {
            (new GlideImage())
                ->load($conversionTempFile, $manipulation)
                ->useAbsoluteSourceFilePath()
                ->save($conversionTempFile);
        }
        return $conversionTempFile;
    }
}