<?php

namespace Spatie\Medialibrary\MediaCollections\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Spatie\Medialibrary\Conversions\Conversion;
use Spatie\Medialibrary\Conversions\ConversionCollection;
use Spatie\Medialibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\Medialibrary\Conversions\FileManipulator;
use Spatie\Medialibrary\MediaRepository;
use Spatie\Medialibrary\MediaCollections\Models\Media;
use Spatie\Medialibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\Medialibrary\ResponsiveImages\RegisteredResponsiveImages;

class CleanCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'medialibrary:clean {modelType?} {collectionName?} {disk?}
    {--dry-run : List files that will be removed without removing them},
    {--force : Force the operation to run when in production},
    {--rate-limit= : Limit the number of request per second }';

    protected $description = 'Clean deprecated conversions and files without related model.';

    protected MediaRepository $mediaRepository;

    protected FileManipulator $fileManipulator;

    protected Factory $fileSystem;

    protected DefaultPathGenerator $basePathGenerator;

    protected bool $isDryRun = false;

    protected int $rateLimit = 0;

    public function __construct(
        MediaRepository $mediaRepository,
        FileManipulator $fileManipulator,
        Factory $fileSystem,
        DefaultPathGenerator $basePathGenerator
    ) {
        parent::__construct();

        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
        $this->fileSystem = $fileSystem;
        $this->basePathGenerator = $basePathGenerator;
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->isDryRun = $this->option('dry-run');
        $this->rateLimit = (int) $this->option('rate-limit');

        $this->deleteFilesGeneratedForDeprecatedConversions();

        $this->deleteOrphanedDirectories();

        $this->info('All done!');
    }

    public function getMediaItems(): Collection
    {
        $modelType = $this->argument('modelType');
        $collectionName = $this->argument('collectionName');

        if (! is_null($modelType) && ! is_null($collectionName)) {
            return $this->mediaRepository->getByModelTypeAndCollectionName(
                $modelType,
                $collectionName
            );
        }

        if (! is_null($modelType)) {
            return $this->mediaRepository->getByModelType($modelType);
        }

        if (! is_null($collectionName)) {
            return $this->mediaRepository->getByCollectionName($collectionName);
        }

        return $this->mediaRepository->all();
    }

    protected function deleteFilesGeneratedForDeprecatedConversions()
    {
        $this->getMediaItems()->each(function (Media $media) {
            $this->deleteConversionFilesForDeprecatedConversions($media);

            if ($media->responsive_images) {
                $this->deleteResponsiveImagesForDeprecatedConversions($media);
            }

            if ($this->rateLimit) {
                usleep((1 / $this->rateLimit) * 1000000 * 2);
            }
        });
    }

    protected function deleteConversionFilesForDeprecatedConversions(Media $media)
    {
        $conversionFilePaths = ConversionCollection::createForMedia($media)->getConversionsFiles($media->collection_name);

        $conversionPath = $this->basePathGenerator->getPathForConversions($media);
        $currentFilePaths = $this->fileSystem->disk($media->disk)->files($conversionPath);

        collect($currentFilePaths)
            ->reject(fn(string $currentFilePath) => $conversionFilePaths->contains(basename($currentFilePath)))
            ->each(function (string $currentFilePath) use ($media) {
                if (! $this->isDryRun) {
                    $this->fileSystem->disk($media->disk)->delete($currentFilePath);

                    $this->markConversionAsRemoved($media, $currentFilePath);
                }

                $this->info("Deprecated conversion file `{$currentFilePath}` ".($this->isDryRun ? 'found' : 'has been removed'));
            });
    }

    protected function deleteResponsiveImagesForDeprecatedConversions(Media $media)
    {
        $conversionNames = ConversionCollection::createForMedia($media)
            ->map(fn(Conversion $conversion) => $conversion->getName())
            ->push('medialibrary_original');

        $responsiveImagesGeneratedFor = array_keys($media->responsive_images);

        collect($responsiveImagesGeneratedFor)
            ->map(fn(string $generatedFor) => $media->responsiveImages($generatedFor))
            ->reject(fn(RegisteredResponsiveImages $responsiveImages) => $conversionNames->contains($responsiveImages->generatedFor))
            ->each(function (RegisteredResponsiveImages $responsiveImages) {
                if (! $this->isDryRun) {
                    $responsiveImages->delete();
                }
            });
    }

    protected function deleteOrphanedDirectories()
    {
        $diskName = $this->argument('disk') ?: config('medialibrary.disk_name');

        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw FileCannotBeAdded::diskDoesNotExist($diskName);
        }
        $mediaClass = config('medialibrary.media_model');
        $mediaInstance = new $mediaClass();
        $keyName = $mediaInstance->getKeyName();

        $mediaIds = collect($this->mediaRepository->all()->pluck($keyName)->toArray());

        collect($this->fileSystem->disk($diskName)->directories())
            ->filter(fn(string $directory) => is_numeric($directory))
            ->reject(fn(string $directory) => $mediaIds->contains((int) $directory))->each(function (string $directory) use ($diskName) {
                if (! $this->isDryRun) {
                    $this->fileSystem->disk($diskName)->deleteDirectory($directory);
                }

                if ($this->rateLimit) {
                    usleep((1 / $this->rateLimit) * 1000000);
                }

                $this->info("Orphaned media directory `{$directory}` ".($this->isDryRun ? 'found' : 'has been removed'));
            });
    }

    protected function markConversionAsRemoved(Media $media, string $conversionPath)
    {
        $conversionFile = pathinfo($conversionPath, PATHINFO_FILENAME);

        $generatedConversionName = null;

        $media->getGeneratedConversions()
            ->filter(fn(bool $isGenerated, string $generatedConversionName) => Str::contains($conversionFile, $generatedConversionName))
            ->each(function (bool $isGenerated, string $generatedConversionName) use ($media) {
                $media->markAsConversionGenerated($generatedConversionName, false);
            });

        $media->save();
    }
}
