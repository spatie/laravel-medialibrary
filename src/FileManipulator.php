<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Events\ConversionWillStart;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;
use Spatie\MediaLibrary\Helpers\ImageFactory;
use Spatie\MediaLibrary\Helpers\TemporaryDirectory;
use Spatie\MediaLibrary\ImageGenerators\ImageGenerator;
use Spatie\MediaLibrary\Jobs\PerformConversions;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImageGenerator;
use Storage;

class FileManipulator
{
    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     * @param array $only
     * @param bool $onlyMissing
     */
    public function createDerivedFiles(Media $media, array $only = [], bool $onlyMissing = false)
    {
        $profileCollection = ConversionCollection::createForMedia($media);

        if (! empty($only)) {
            $profileCollection = $profileCollection->filter(function ($collection) use ($only) {
                return in_array($collection->getName(), $only);
            });
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
     * @param \Spatie\MediaLibrary\Conversion\ConversionCollection $conversions
     * @param \Spatie\MediaLibrary\Models\Media $media
     * @param bool $onlyMissing
     */
    public function performConversions(ConversionCollection $conversions, Media $media, bool $onlyMissing = false)
    {
        if ($conversions->isEmpty()) {
            return;
        }

        $imageGenerator = $this->determineImageGenerator($media);

        if (! $imageGenerator) {
            return;
        }

        $temporaryDirectory = TemporaryDirectory::create();

        $copiedOriginalFile = app(Filesystem::class)->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(Str::random(16).'.'.$media->extension)
        );

        $conversions
            ->reject(function (Conversion $conversion) use ($onlyMissing, $media) {
                $relativePath = $media->getPath($conversion->getName());

                $rootPath = config('filesystems.disks.'.$media->disk.'.root');

                if ($rootPath) {
                    $relativePath = str_replace($rootPath, '', $relativePath);
                }

                return $onlyMissing && Storage::disk($media->disk)->exists($relativePath);
            })
            ->each(function (Conversion $conversion) use ($media, $imageGenerator, $copiedOriginalFile) {
                event(new ConversionWillStart($media, $conversion, $copiedOriginalFile));

                $copiedOriginalFile = $imageGenerator->convert($copiedOriginalFile, $conversion);

                $manipulationResult = $this->performManipulations($media, $conversion, $copiedOriginalFile);

                $newFileName = $conversion->getConversionFile($media->file_name);

                $renamedFile = MediaLibraryFileHelper::renameInDirectory($manipulationResult, $newFileName);

                if ($conversion->shouldGenerateResponsiveImages()) {
                    app(ResponsiveImageGenerator::class)->generateResponsiveImagesForConversion(
                        $media,
                        $conversion,
                        $renamedFile
                    );
                }

                app(Filesystem::class)->copyToMediaLibrary($renamedFile, $media, 'conversions');

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

        $conversionTempFile = pathinfo($imageFile, PATHINFO_DIRNAME).'/'.Str::random(16)
            .$conversion->getName()
            .'.'
            .$media->extension;

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
        $performConversionsJobClass = config('medialibrary.jobs.perform_conversions', PerformConversions::class);

        $job = new $performConversionsJobClass($queuedConversions, $media, $onlyMissing);

        if ($customQueue = config('medialibrary.queue_name')) {
            $job->onQueue($customQueue);
        }

        app(Dispatcher::class)->dispatch($job);
    }

    /**
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return \Spatie\MediaLibrary\ImageGenerators\ImageGenerator|null
     */
    public function determineImageGenerator(Media $media)
    {
        return $media->getImageGenerators()
            ->map(function (string $imageGeneratorClassName) {
                return app($imageGeneratorClassName);
            })
            ->first(function (ImageGenerator $imageGenerator) use ($media) {
                return $imageGenerator->canConvert($media);
            });
    }
}
