<?php

namespace Spatie\MediaLibrary;

use Spatie\Glide\GlideImage;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Jobs\PerformConversions;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;

class FileManipulator
{
    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ConversionCollection::createForMedia($media);

        $this->performConversions(
            $profileCollection->getNonQueuedConversions($media->collection_name),
            $media
        );

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if (count($queuedConversions)) {
            $this->dispatchQueuedConversions($media, $queuedConversions);
        }
    }

    /**
     * Perform the given conversions for the given media.
     *
     * @param \Spatie\MediaLibrary\Conversion\ConversionCollection $conversions
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function performConversions(ConversionCollection $conversions, Media $media)
    {
        $imageGenerator = $this->determineImageGenerator($media);

        if (! $imageGenerator || $conversions->isEmpty()) {
            return;
        }

        $tempDirectory = $this->createTempDirectory();

        $copiedOriginalFile = $tempDirectory.'/'.str_random(16).'.'.$media->extension;

        app(FilesystemInterface::class)->copyFromMediaLibrary($media, $copiedOriginalFile);

        foreach ($conversions as $conversion) {
            $copiedOriginalFile = $imageGenerator->convert($copiedOriginalFile, $conversion);

            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            $renamedFile = MediaLibraryFileHelper::renameInDirectory($conversionResult, $conversion->getName().'.'.
                $conversion->getResultExtension(pathinfo($copiedOriginalFile, PATHINFO_EXTENSION)));

            app(FilesystemInterface::class)->copyToMediaLibrary($renamedFile, $media, true);

            event(new ConversionHasBeenCompleted($media, $conversion));
        }

        File::deleteDirectory($tempDirectory);
    }

    /**
     * Perform the conversion.
     *
     * @param \Spatie\MediaLibrary\Media $media
     * @param Conversion $conversion
     * @param string $copiedOriginalFile
     *
     * @return string
     */
    public function performConversion(Media $media, Conversion $conversion, string $copiedOriginalFile)
    {
        $conversionTempFile = pathinfo($copiedOriginalFile, PATHINFO_DIRNAME).'/'.string()->random(16).
            $conversion->getName().'.'.$media->extension;

        File::copy($copiedOriginalFile, $conversionTempFile);

        foreach ($conversion->getManipulations() as $manipulation) {
            GlideImage::create($conversionTempFile)
                ->modify($manipulation)
                ->save($conversionTempFile);
        }

        return $conversionTempFile;
    }

    /*
     * Create a directory to store some working files.
     */
    public function createTempDirectory() : string
    {
        $tempDirectory = storage_path('medialibrary/temp/'.str_random(16));

        File::makeDirectory($tempDirectory, 493, true);

        return $tempDirectory;
    }

    /*
     * Dispatch the given conversions.
     */
    protected function dispatchQueuedConversions(Media $media, ConversionCollection $queuedConversions)
    {
        $job = new PerformConversions($queuedConversions, $media);

        $customQueue = config('laravel-medialibrary.queue_name');

        if ($customQueue != '') {
            $job->onQueue($customQueue);
        }

        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
    }

    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return \Spatie\MediaLibrary\ImageGenerators\ImageGenerator|null
     */
    public function determineImageGenerator(Media $media)
    {
        $imageGenerators = $media->getImageGenerators()
            ->map(function (string $imageGeneratorClassName) {
                return app($imageGeneratorClassName);
            });

        foreach ($imageGenerators as $imageGenerator) {
            if ($imageGenerator->canConvert($media)) {
                return $imageGenerator;
            }
        }
    }
}
