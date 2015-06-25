<?php

namespace Spatie\MediaLibrary;

use Spatie\Glide\GlideImage;

class MediaLibraryFileManipulator
{
    public function createDerivedFilesForMedia(Media $media)
    {
        foreach(ProfileProperties::getForMedia($media) as $collectionName => $profiles) {

            if ($media->collection_name == $collectionName) {

                foreach($profiles as $profileName => $profileProperties) {

                    $tempFile = storage_path('media-library/temp/'  . $profileName . '.jpg');

                    (new GlideImage())
                        ->load($media->getPath(), $profileProperties)
                        ->useAbsoluteSourceFilePath()
                        ->save($tempFile);

                    app('MediaLibraryFileSystem')->copyFileToMediaLibraryForMedia($tempFile, $media);

                    unlink($tempFile);
                }
            }
        }
    }
}