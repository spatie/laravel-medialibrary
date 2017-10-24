<?php

namespace Spatie\MediaLibrary;

use Spatie\Image\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Bus\Dispatcher;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Jobs\PerformConversions;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\MediaLibrary\Events\ConversionWillStart;
use Spatie\MediaLibrary\ImageGenerators\ImageGenerator;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;

class FileManipulator
{
    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     * @param bool $onlyIfMissing
     */
    public function createDerivedFiles(Media $media, array $only = [], $onlyIfMissing = false)
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
            $onlyIfMissing
        );

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if ($queuedConversions->isNotEmpty()) {
            $this->dispatchQueuedConversions($media, $queuedConversions);
        }
    }

    /**
     * Perform the given conversions for the given media.
     *
     * @param \Spatie\MediaLibrary\Conversion\ConversionCollection $conversions
     * @param \Spatie\MediaLibrary\Media $media
     * @param bool $onlyIfMissing
     */
    public function performConversions(ConversionCollection $conversions, Media $media, $onlyIfMissing = false)
    {
        if ($conversions->isEmpty()) {
            return;
        }

        $imageGenerator = $this->determineImageGenerator($media);

        if (! $imageGenerator) {
            return;
        }

        $temporaryDirectory = new TemporaryDirectory($this->getTemporaryDirectoryPath());

        $copiedOriginalFile = app(Filesystem::class)->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(str_random(16).'.'.$media->extension)
        );

        $conversions
            ->reject(function(Conversion $conversion) use ($onlyIfMissing, $media) {
                return $onlyIfMissing && file_exists($media->getPath($conversion->getName()));
            })
            ->each(function(Conversion $conversion) use ($media, $imageGenerator, $copiedOriginalFile) {
                event(new ConversionWillStart($media, $conversion));

                $copiedOriginalFile = $imageGenerator->convert($copiedOriginalFile, $conversion);

                $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

                $newFileName = $conversion->getName()
                    .'.'
                    .$conversion->getResultExtension(pathinfo($copiedOriginalFile, PATHINFO_EXTENSION));

                $renamedFile = MediaLibraryFileHelper::renameInDirectory($conversionResult, $newFileName);

                app(Filesystem::class)->copyToMediaLibrary($renamedFile, $media, true);

                event(new ConversionHasBeenCompleted($media, $conversion));
            });

        $temporaryDirectory->delete();
    }

    public function performConversion(Media $media, Conversion $conversion, string $imageFile): string
    {
        $conversionTempFile = pathinfo($imageFile, PATHINFO_DIRNAME).'/'.str_random(16)
            .$conversion->getName()
            .'.'
            .$media->extension;

        File::copy($imageFile, $conversionTempFile);

        $supportedFormats = ['jpg', 'pjpg', 'png', 'gif'];
        if ($conversion->shouldKeepOriginalImageFormat() && in_array($media->extension, $supportedFormats)) {
            $conversion->format($media->extension);
        }

        Image::load($conversionTempFile)
            ->useImageDriver(config('medialibrary.image_driver'))
            ->manipulate($conversion->getManipulations())
            ->save();

        return $conversionTempFile;
    }

    protected function dispatchQueuedConversions(Media $media, ConversionCollection $queuedConversions)
    {
        $job = new PerformConversions($queuedConversions, $media);

        if ($customQueue = config('medialibrary.queue_name')) {
            $job->onQueue($customQueue);
        }

        app(Dispatcher::class)->dispatch($job);
    }

    protected function getTemporaryDirectoryPath(): string
    {
        $path = is_null(config('medialibrary.temporary_directory_path'))
            ? storage_path('medialibrary/temp')
            : config('medialibrary.temporary_directory_path');

        return $path.DIRECTORY_SEPARATOR.str_random(32);
    }

    /**
     * @param \Spatie\MediaLibrary\Media $media
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
