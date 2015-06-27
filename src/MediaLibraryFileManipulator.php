<?php

namespace Spatie\MediaLibrary;

use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Profile\ProfileCollectionFactory;

class MediaLibraryFileManipulator
{
    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ProfileCollectionFactory::createForMedia($media);

        foreach ($profileCollection->getConversionsForCollection($media->collection_name) as $conversion) {

            $tempFile = storage_path('media-library/temp/' . $media->id .'-' . $conversion->getName() . '.jpg');

            /*
             * @todo make this working with cloud systems
             */
            copy($media->getPath(), $tempFile);

            foreach($conversion->getManipulations() as $manipulation)
            {
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