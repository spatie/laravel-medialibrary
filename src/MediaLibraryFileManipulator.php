<?php

namespace Spatie\MediaLibrary;

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
        foreach ($conversions as $conversion) {

            $tempFile = storage_path('media-library/temp/' . $media->id . '-' . $conversion->getName() . '.jpg');

            /*
             * @todo make this working with cloud systems
             */
            copy($media->getPath(), $tempFile);

            foreach ($conversion->getManipulations() as $manipulation) {
                (new GlideImage())
                    ->load($tempFile, $manipulation)
                    ->useAbsoluteSourceFilePath()
                    ->save($tempFile);
            }

            app(MediaLibraryFileSystem::class)->copyFileToMediaLibraryForMedia($tempFile, $media);

            unlink($tempFile);
        }
    }


}