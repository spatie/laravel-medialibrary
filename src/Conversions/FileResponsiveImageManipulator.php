<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;

class FileResponsiveImageManipulator
{
    public function createDerivedFiles(Media $media, array $only = [], bool $onlyMissing = false)
    {
        $processFiles = true;

        // generate for a specific collection
        if (count($only) && !in_array($media->collection_name, $only)) {
            $processFiles = false;
        }
        
        // generate for only missing
        if ($onlyMissing && count($media->responsive_images)) {
            $processFiles = false;
        }

        return !$processFiles ?: $this->dispatch($media);
    }

    protected function dispatch(Media $media)
    {
        $performConversionsJobClass = config('media-library.jobs.generate_responsive_images', GenerateResponsiveImagesJob::class);

        $job = new $performConversionsJobClass($media);

        if ($customQueue = config('media-library.queue_name')) {
            $job->onQueue($customQueue);
        }

        dispatch($job);
    }
}
