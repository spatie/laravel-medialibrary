<?php

namespace Spatie\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\MediaRepository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;
use Spatie\MediaLibrary\ResponsiveImages\RegisteredResponsiveImages;

class CleanCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'medialibrary:clean {modelType?} {collectionName?} {disk?}
    {--dry-run : List files that will be removed without removing them},
    {--force : Force the operation to run when in production},
    {--rate-limit= : Limit the number of request per second }';

    protected $description = 'Clean deprecated conversions and files without related model.';

    /** @var \Spatie\MediaLibrary\MediaRepository */
    protected $mediaRepository;

    /** @var \Spatie\MediaLibrary\FileManipulator */
    protected $fileManipulator;

    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $fileSystem;

    /** @var \Spatie\MediaLibrary\PathGenerator\BasePathGenerator */
    protected $basePathGenerator;

    /** @var bool */
    protected $isDryRun = false;

    /** @var int */
    protected $rateLimit = 0;

    /**
     * @param \Spatie\MediaLibrary\MediaRepository                 $mediaRepository
     * @param \Spatie\MediaLibrary\FileManipulator                 $fileManipulator
     * @param \Illuminate\Contracts\Filesystem\Factory             $fileSystem
     * @param \Spatie\MediaLibrary\PathGenerator\BasePathGenerator $basePathGenerator
     */
    public function __construct(
        MediaRepository $mediaRepository,
        FileManipulator $fileManipulator,
        Factory $fileSystem,
        BasePathGenerator $basePathGenerator
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
            ->reject(function (string $currentFilePath) use ($conversionFilePaths) {
                return $conversionFilePaths->contains(basename($currentFilePath));
            })
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
            ->map(function (Conversion $conversion) {
                return $conversion->getName();
            })
            ->push('medialibrary_original');

        $responsiveImagesGeneratedFor = array_keys($media->responsive_images);

        collect($responsiveImagesGeneratedFor)
            ->map(function (string $generatedFor) use ($media) {
                return $media->responsiveImages($generatedFor);
            })
            ->reject(function (RegisteredResponsiveImages $responsiveImages) use ($conversionNames) {
                return $conversionNames->contains($responsiveImages->generatedFor);
            })
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

        $mediaIds = collect($this->mediaRepository->all()->pluck('id')->toArray());

        collect($this->fileSystem->disk($diskName)->directories())
            ->filter(function (string $directory) {
                return is_numeric($directory);
            })
            ->reject(function (string $directory) use ($mediaIds) {
                return $mediaIds->contains((int) $directory);
            })->each(function (string $directory) use ($diskName) {
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
            ->filter(function (bool $isGenerated, string $generatedConversionName) use ($conversionFile) {
                return str_contains($conversionFile, $generatedConversionName);
            })
            ->each(function (bool $isGenerated, string $generatedConversionName) use ($media) {
                $media->markAsConversionGenerated($generatedConversionName, false);
            });

        $media->save();
    }
}
