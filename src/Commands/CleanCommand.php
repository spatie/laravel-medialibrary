<?php

namespace Spatie\MediaLibrary\Commands;

use Spatie\MediaLibrary\Media;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\MediaRepository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class CleanCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'medialibrary:clean {modelType?} {collectionName?} {disk?} 
    {--dry-run : List files that will be removed without removing them},
    {--force : Force the operation to run when in production}';

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

        $this->deleteFilesGeneratedForDeprecatedConversions();

        $this->deleteOrphanedFiles();

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
            $conversionFilePaths = ConversionCollection::createForMedia($media)->getConversionsFiles($media->collection_name);

            $path = $this->basePathGenerator->getPathForConversions($media);
            $currentFilePaths = $this->fileSystem->disk($media->disk)->files($path);

            collect($currentFilePaths)
                ->reject(function (string $currentFilePath) use ($conversionFilePaths) {
                    return  $conversionFilePaths->contains(basename($currentFilePath));
                })
                ->each(function (string $currentFilePath) use ($media) {
                    if (! $this->isDryRun) {
                        $this->fileSystem->disk($media->disk)->delete($currentFilePath);
                    }

                    $this->info("Deprecated conversion file `{$currentFilePath}` ".($this->isDryRun ? 'found' : 'has been removed'));
                });
        });
    }

    protected function deleteOrphanedFiles()
    {
        $diskName = $this->argument('disk') ?: config('medialibrary.default_filesystem');

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

                $this->info("Orphaned media directory `{$directory}` ".($this->isDryRun ? 'found' : 'has been removed'));
            });
    }
}
