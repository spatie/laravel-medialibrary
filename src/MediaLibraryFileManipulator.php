<?php

namespace Spatie\MediaLibrary;

use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Profile\ProfileCollectionFactory;

class MediaLibraryFileManipulator
{
    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ProfileCollectionFactory::createForMedia($media);

        $conversions = $profileCollection->getConversionsForCollection($media->collection_name);

        $nonQueuedConversions = $conversions->filter(function(Conversion $conversion) {
            return ! $conversion->shouldBeQueued();
        });

        $queuedConversions = $conversions->filter(function(Conversion $conversion) {
            return $conversion->shouldBeQueued();
        });

        foreach ($nonQueuedConversions as $conversion) {

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