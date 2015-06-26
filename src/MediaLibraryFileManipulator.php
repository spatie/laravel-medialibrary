<?php

namespace Spatie\MediaLibrary;

use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Profile\ProfileCollectionFactory;

class MediaLibraryFileManipulator
{
    public function createDerivedFiles(Media $media)
    {

        $profileCollection = ProfileCollectionFactory::createForMedia($media);

        foreach ($profileCollection->getProfilesForCollection($media->collection_name) as $profile) {

            $tempFile = storage_path('media-library/temp/' . $media->id .'-' . $profile->name . '.jpg');

            /*
             * @todo make this working with cloud systems
             */
            copy($media->getPath(), $tempFile);

            foreach($profile->getConversion as $conversion)
            {
                (new GlideImage())
                    ->load($tempFile, $conversion)
                    ->useAbsoluteSourceFilePath()
                    ->save($tempFile);
            }


            app(MediaLibraryFileSystem::class)->copyFileToMediaLibraryForMedia($tempFile, $media);

            unlink($tempFile);


        }

    }
}