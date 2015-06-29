<?php

namespace Spatie\MediaLibrary;

use File;
use GlideImage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;

class FileManipulator
{
    use DispatchesJobs;

    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ConversionCollectionFactory::createForMedia($media);

        echo 'profile collection created';

        $this->performConversions($profileCollection->getNonQueuedConversions($media->collection_name), $media);

        echo 'non queued ready';

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if (count($queuedConversions)) {
            $this->dispatch(new PerformConversions($queuedConversions, $media));
        }
    }

    public function performConversions($conversions, $media)
    {
        $tempDirectory = storage_path('media-library/temp/' . str_random(16));

        $copiedOriginalFile = storage_path('media-library/temp/' . str_random(16) . '.' . pathinfo($media->file, PATHINFO_EXTENSION));

        app(FileSystem::class)->copyFromMediaLibrary($media, $copiedOriginalFile);

        foreach ($conversions as $conversion) {

            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            app(FileSystem::class)->copyToMediaLibrary($conversionResult, $media);

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