<?php

namespace Spatie\MediaLibrary\Conversions;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;

class FileResponsiveImageManipulator
{
    public function createDerivedFiles(Media $media, array $only = [], bool $onlyMissing = false)
    {
        if (
            empty($only) ||
            (!empty($only) && in_array($media->collection_name, $only)) ||
            ($onlyMissing && empty($media->responsive_images))
        ) {
            return $this->dispatch($media);
        }
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
