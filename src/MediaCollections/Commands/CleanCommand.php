<?php

namespace Spatie\MediaLibrary\MediaCollections\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\RegisteredResponsiveImages;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class CleanCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'media-library:clean {modelType?} {collectionName?} {disk?}
    {--dry-run : List files that will be removed without removing them},
    {--force : Force the operation to run when in production},
    {--rate-limit= : Limit the number of requests per second },
    {--skip-conversions : Do not remove deprecated conversions}';

    protected $description = 'Clean deprecated conversions and files without related model.';

    protected MediaRepository $mediaRepository;

    protected FileManipulator $fileManipulator;

    protected Factory $fileSystem;

    protected bool $isDryRun = false;

    protected int $rateLimit = 0;

    public function handle(
        MediaRepository $mediaRepository,
        FileManipulator $fileManipulator,
        Factory $fileSystem,
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
        $this->fileSystem = $fileSystem;

        if (! $this->confirmToProceed()) {
            return;
        }

        $this->isDryRun = $this->option('dry-run');
        $this->rateLimit = (int) $this->option('rate-limit');

        if (! $this->option('skip-conversions')) {
            $this->deleteFilesGeneratedForDeprecatedConversions();
        }

        $this->deleteOrphanedDirectories();

        $this->info('All done!');
    }

    /** @return Collection<int, Media> */
    public function getMediaItems(): Collection
    {
        $modelType = $this->argument('modelType');
        $collectionName = $this->argument('collectionName');

        if (is_string($modelType) && is_string($collectionName)) {
            return $this->mediaRepository->getByModelTypeAndCollectionName(
                $modelType,
                $collectionName
            );
        }

        if (is_string($modelType)) {
            return $this->mediaRepository->getByModelType($modelType);
        }

        if (is_string($collectionName)) {
            return $this->mediaRepository->getByCollectionName($collectionName);
        }

        return $this->mediaRepository->all();
    }

    protected function deleteFilesGeneratedForDeprecatedConversions(): void
    {
        $this->getMediaItems()->each(function (Media $media) {
            $this->deleteConversionFilesForDeprecatedConversions($media);

            if ($media->responsive_images) {
                $this->deleteDeprecatedResponsiveImages($media);
            }

            if ($this->rateLimit) {
                usleep((1 / $this->rateLimit) * 1_000_000 * 2);
            }
        });
    }

    protected function deleteConversionFilesForDeprecatedConversions(Media $media): void
    {
        $conversionFilePaths = ConversionCollection::createForMedia($media)->getConversionsFiles($media->collection_name);

        $conversionPath = PathGeneratorFactory::create($media)->getPathForConversions($media);
        $currentFilePaths = $this->fileSystem->disk($media->disk)->files($conversionPath);

        collect($currentFilePaths)
            ->reject(fn (string $currentFilePath) => $conversionFilePaths->contains(basename($currentFilePath)))
            ->reject(fn (string $currentFilePath) => $media->file_name === basename($currentFilePath))
            ->each(function (string $currentFilePath) use ($media) {
                if (! $this->isDryRun) {
                    $this->fileSystem->disk($media->disk)->delete($currentFilePath);

                    $this->markConversionAsRemoved($media, $currentFilePath);
                }

                $this->info("Deprecated conversion file `{$currentFilePath}` ".($this->isDryRun ? 'found' : 'has been removed'));
            });
    }

    protected function deleteDeprecatedResponsiveImages(Media $media): void
    {
        $conversionNamesWithResponsiveImages = ConversionCollection::createForMedia($media)
            ->filter(fn (Conversion $conversion) => $conversion->shouldGenerateResponsiveImages())
            ->map(fn (Conversion $conversion) => $conversion->getName())
            ->push('media_library_original');

        /** @var array<int, string> $responsiveImagesGeneratedFor */
        $responsiveImagesGeneratedFor = array_keys($media->responsive_images);

        collect($responsiveImagesGeneratedFor)
            ->map(fn (string $generatedFor) => $media->responsiveImages($generatedFor))
            ->reject(fn (RegisteredResponsiveImages $responsiveImages) => $conversionNamesWithResponsiveImages->contains($responsiveImages->generatedFor))
            ->each(function (RegisteredResponsiveImages $responsiveImages) {
                if (! $this->isDryRun) {
                    $responsiveImages->delete();
                }
            });
    }

    protected function deleteOrphanedDirectories(): void
    {
        $diskName = $this->argument('disk') ?: config('media-library.disk_name');

        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw DiskDoesNotExist::create($diskName);
        }
        $mediaClass = config('media-library.media_model');
        $mediaInstance = new $mediaClass();
        $keyName = $mediaInstance->getKeyName();

        $mediaIds = collect($this->mediaRepository->all()->pluck($keyName)->toArray());

        /** @var array<int, string> */
        $directories = $this->fileSystem->disk($diskName)->directories();

        collect($directories)
            ->filter(fn (string $directory) => is_numeric($directory))
            ->reject(fn (string $directory) => $mediaIds->contains((int) $directory))
            ->each(function (string $directory) use ($diskName) {
                if (! $this->isDryRun) {
                    $this->fileSystem->disk($diskName)->deleteDirectory($directory);
                }

                if ($this->rateLimit) {
                    usleep((1 / $this->rateLimit) * 1_000_000);
                }

                $this->info("Orphaned media directory `{$directory}` ".($this->isDryRun ? 'found' : 'has been removed'));
            });
    }

    protected function markConversionAsRemoved(Media $media, string $conversionPath): void
    {
        $conversionFile = pathinfo($conversionPath, PATHINFO_FILENAME);

        $generatedConversionName = null;

        $media->getGeneratedConversions()
            ->filter(
                fn (bool $isGenerated, string $generatedConversionName) => Str::contains($conversionFile, $generatedConversionName)
            )
            ->each(
                fn (bool $isGenerated, string $conversionName) => $media->markAsConversionNotGenerated($conversionName)
            );

        $media->save();
    }
}
