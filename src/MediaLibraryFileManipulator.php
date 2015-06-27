<?php

namespace Spatie\MediaLibrary;

use DispatchesJobs;

class MediaLibraryFileManipulator
{
    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ProfileCollectionFactory::createForMedia($media);

        $this->performConversions($profileCollection->getNonQueuedConversions($media->collection_name), $media);

        $this->queue($profileCollection->getNonQueuedConversions($media->collection_name), $media);

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