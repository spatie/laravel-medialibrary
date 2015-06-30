<?php

namespace Spatie\MediaLibrary;

use File;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\Glide\GlideImage;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Conversion\ConversionCollectionFactory;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;
use Spatie\MediaLibrary\Helpers\Gitignore;
use Spatie\MediaLibrary\Jobs\PerformConversions;

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

    public function performConversions(ConversionCollection $conversions, Media $media)
    {
        $tempDirectory = $this->createTempDirectory();

        $copiedOriginalFile = storage_path('media-library/temp/' . str_random(16) . '.' . $media->getExtension());

        app(FileSystem::class)->copyFromMediaLibrary($media, $copiedOriginalFile);

        foreach ($conversions as $conversion) {

            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            $renamedFile = MediaLibraryFileHelper::renameInDirectory($conversionResult, $conversion->getName() . '.jpg');

            echo 'conversion done, copy to medialib starting';
            app(FileSystem::class)->copyToMediaLibrary($renamedFile, $media, 'conversions');
            echo 'copy done';

        }

        File::deleteDirectory($tempDirectory);
    }

    /**
     * @param $media
     * @param $conversion
     * @param $copiedOriginalFile
     * @return string
     */
    public function performConversion(Media $media, Conversion $conversion, $copiedOriginalFile)
    {
        $conversionTempFile = storage_path('media-library/temp/' . string()->random(16) . $conversion->getName() . '.' . $media->getExtension());

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
     * @return string
     */
    public function createTempDirectory()
    {
        $tempDirectory = storage_path('media-library/temp/' . str_random(16));

        File::makeDirectory($tempDirectory, 493, true);

        Gitignore::createIn(storage_path('media-library'));

        return $tempDirectory;
    }
}