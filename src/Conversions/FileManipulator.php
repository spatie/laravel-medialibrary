<?php

namespace Spatie\Medialibrary\Conversions;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Medialibrary\Events\ConversionHasBeenCompleted;
use Spatie\Medialibrary\Events\ConversionWillStart;
use Spatie\Medialibrary\Filesystem\Filesystem;
use Spatie\Medialibrary\ImageGenerators\ImageGenerator;
use Spatie\Medialibrary\ImageGenerators\ImageGeneratorFactory;
use Spatie\Medialibrary\Support\File as MedialibraryFileHelper;
use Spatie\Medialibrary\Support\ImageFactory;
use Spatie\Medialibrary\Support\TemporaryDirectory;
use Spatie\Medialibrary\Jobs\PerformConversionsJob;
use Spatie\Medialibrary\Models\Media;
use Spatie\Medialibrary\ResponsiveImages\ResponsiveImageGenerator;
use Illuminate\Support\Facades\Storage;

class FileManipulator
{
    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\Medialibrary\Models\Media $media
     * @param array $only
     * @param bool $onlyMissing
     */
    public function createDerivedFiles(Media $media, array $only = [], bool $onlyMissing = false): void
    {
        $profileCollection = ConversionCollection::createForMedia($media);

        if (!empty($only)) {
            $profileCollection = $profileCollection->filter(fn($collection) => in_array($collection->getName(), $only));
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

    /**
     * Perform the given conversions for the given media.
     *
     * @param \Spatie\Medialibrary\Conversions\ConversionCollection $conversions
     * @param \Spatie\Medialibrary\Models\Media $media
     * @param bool $onlyMissing
     */
    public function performConversions(ConversionCollection $conversions, Media $media, bool $onlyMissing = false)
    {
        if ($conversions->isEmpty()) {
            return;
        }

        $imageGenerator = ImageGeneratorFactory::forMedia($media);

        if (!$imageGenerator) {
            return;
        }

        $temporaryDirectory = TemporaryDirectory::create();

        $copiedOriginalFile = app(Filesystem::class)->copyFromMedialibrary(
            $media,
            $temporaryDirectory->path(Str::random(16) . '.' . $media->extension)
        );

        $conversions
            ->reject(function (Conversion $conversion) use ($onlyMissing, $media) {
                $relativePath = $media->getPath($conversion->getName());

                $rootPath = config('filesystems.disks.' . $media->disk . '.root');

                if ($rootPath) {
                    $relativePath = str_replace($rootPath, '', $relativePath);
                }

                return $onlyMissing && Storage::disk($media->disk)->exists($relativePath);
            })
            ->each(function (Conversion $conversion) use ($media, $imageGenerator, $copiedOriginalFile) {
                event(new ConversionWillStart($media, $conversion, $copiedOriginalFile));

                $copiedOriginalFile = $imageGenerator->convert($copiedOriginalFile, $conversion);

                $manipulationResult = $this->performManipulations($media, $conversion, $copiedOriginalFile);

                $newFileName = $conversion->getConversionFile($media);

                $renamedFile = $this->renameInLocalDirectory($manipulationResult, $newFileName);

                if ($conversion->shouldGenerateResponsiveImages()) {
                    app(ResponsiveImageGenerator::class)->generateResponsiveImagesForConversion(
                        $media,
                        $conversion,
                        $renamedFile
                    );
                }

                app(Filesystem::class)->copyToMedialibrary($renamedFile, $media, 'conversions');

                $media->markAsConversionGenerated($conversion->getName(), true);

                event(new ConversionHasBeenCompleted($media, $conversion));
            });

        $temporaryDirectory->delete();
    }

    public function performManipulations(Media $media, Conversion $conversion, string $imageFile): string
    {
        if ($conversion->getManipulations()->isEmpty()) {
            return $imageFile;
        }

        $conversionTempFile = pathinfo($imageFile, PATHINFO_DIRNAME) . '/' . Str::random(16)
            . $conversion->getName()
            . '.'
            . $media->extension;

        File::copy($imageFile, $conversionTempFile);

        $supportedFormats = ['jpg', 'pjpg', 'png', 'gif'];
        if ($conversion->shouldKeepOriginalImageFormat() && in_array($media->extension, $supportedFormats)) {
            $conversion->format($media->extension);
        }

        ImageFactory::load($conversionTempFile)
            ->manipulate($conversion->getManipulations())
            ->save();

        return $conversionTempFile;
    }

    protected function dispatchQueuedConversions(Media $media, ConversionCollection $queuedConversions, bool $onlyMissing = false)
    {
        $performConversionsJobClass = config('medialibrary.jobs.perform_conversions', PerformConversionsJob::class);

        $job = new $performConversionsJobClass($queuedConversions, $media, $onlyMissing);

        if ($customQueue = config('medialibrary.queue_name')) {
            $job->onQueue($customQueue);
        }

        app(Dispatcher::class)->dispatch($job);
    }

    protected function renameInLocalDirectory(
        string $fileNameWithDirectory,
        string $newFileNameWithoutDirectory): string
    {
        $targetFile = pathinfo($fileNameWithDirectory, PATHINFO_DIRNAME) . '/' . $newFileNameWithoutDirectory;

        rename($fileNameWithDirectory, $targetFile);

        return $targetFile;
    }
}
