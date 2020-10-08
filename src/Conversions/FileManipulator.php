<?php

namespace Spatie\MediaLibrary\Conversions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Actions\PerformConversionAction;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGeneratorFactory;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryDirectory;

class FileManipulator
{
    public function createDerivedFiles(
        Media $media,
        array $only = [],
        bool $onlyMissing = false
    ): void {
        $profileCollection = ConversionCollection::createForMedia($media);

        if (! empty($only)) {
            $profileCollection = $profileCollection->filter(
                fn (Conversion $conversion) => in_array($conversion->getName(), $only)
            );
        }

        $this->performConversions(
            $profileCollection->getNonQueuedConversions($media->collection_name),
            $media,
            $onlyMissing
        );

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if ($queuedConversions->isNotEmpty()) {
            $this->dispatchQueuedConversions($media, $queuedConversions, $onlyMissing);
        }
    }

    public function performConversions(
        ConversionCollection $conversions,
        Media $media,
        bool $onlyMissing = false
    ): void {
        if ($conversions->isEmpty()) {
            return;
        }

        $imageGenerator = ImageGeneratorFactory::forMedia($media);

        if (! $imageGenerator) {
            return;
        }

        $temporaryDirectory = TemporaryDirectory::create();

        $copiedOriginalFile = $this->filesystem()->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(Str::random(32).'.'.$media->extension)
        );

        $conversions
            ->reject(function (Conversion $conversion) use ($onlyMissing, $media) {
                $relativePath = $media->getPath($conversion->getName());

                $rootPath = config("filesystems.disks.{$media->disk}.root");

                if ($rootPath) {
                    $relativePath = str_replace($rootPath, '', $relativePath);
                }

                return $onlyMissing && Storage::disk($media->disk)->exists($relativePath);
            })
            ->each(function (Conversion $conversion) use ($media, $imageGenerator, $copiedOriginalFile) {
                (new PerformConversionAction)
                    ->execute($conversion, $media, $imageGenerator, $copiedOriginalFile);
            });

        $temporaryDirectory->delete();
    }

    protected function dispatchQueuedConversions(
        Media $media,
        ConversionCollection $queuedConversions,
        bool $onlyMissing = false
    ): void {
        $performConversionsJobClass = config('media-library.jobs.perform_conversions', PerformConversionsJob::class);

        $job = new $performConversionsJobClass($queuedConversions, $media, $onlyMissing);

        if ($customQueue = config('media-library.queue_name')) {
            $job->onQueue($customQueue);
        }

        dispatch($job);
    }

    protected function filesystem(): Filesystem
    {
        return app(Filesystem::class);
    }
}
