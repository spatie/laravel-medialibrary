<?php

namespace Spatie\MediaLibrary\Commands;

use Spatie\MediaLibrary\Media;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\MediaRepository;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\PathGenerator\PathGeneratorFactory;

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

    /** @var \Spatie\MediaLibrary\Filesystem\Filesystem */
    protected $mediaFileSystem;

    /** @var \Spatie\MediaLibrary\PathGenerator\PathGenerator */
    protected $pathGenerator;

    /** @var bool */
    protected $isDryRun = false;

    /**
     * @param \Spatie\MediaLibrary\MediaRepository $mediaRepository
     * @param \Spatie\MediaLibrary\FileManipulator $fileManipulator
     * @param \Spatie\MediaLibrary\Filesystem\Filesystem $mediaFileSystem
     * @param \Illuminate\Contracts\Filesystem\Factory $fileSystem
     */
    public function __construct(
        MediaRepository $mediaRepository,
        FileManipulator $fileManipulator,
        Filesystem $mediaFileSystem,
        Factory $fileSystem
    ) {
        parent::__construct();

        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
        $this->mediaFileSystem = $mediaFileSystem;
        $this->fileSystem = $fileSystem;
        $this->pathGenerator = PathGeneratorFactory::create();
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

            $path = $this->pathGenerator->getPathForConversions($media);
            $currentFilePaths = $this->fileSystem->disk($media->disk)->files($path);

            collect($currentFilePaths)
                ->reject(function (string $currentFilePath) use ($conversionFilePaths) {
                    return $conversionFilePaths->contains(basename($currentFilePath));
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
        $diskName = $this->argument('disk') ?: config('medialibrary.defaultFilesystem');

        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw FileCannotBeAdded::diskDoesNotExist($diskName);
        }

        $medias = $this->mediaRepository->all();

        $mediaPaths = $medias->map(function ($media) {
            return rtrim($this->pathGenerator->getPath($media), '/');
        });

        $mediaPaths = $mediaPaths->merge($medias->map(function ($media) {
            return rtrim($this->pathGenerator->getPathForConversions($media), '/');
        }));

        $diff = collect($this->fileSystem->disk($diskName)->allDirectories())->diff($mediaPaths);

        $diff->reject(function (string $directory) use ($diskName) {
            return empty($this->fileSystem->disk($diskName)->files($directory));
        })->each(function (string $directory) use ($diskName) {
            if (! $this->isDryRun) {
                $this->mediaFileSystem->removeDirectory($directory, $diskName);
            }

            $this->info("Orphaned media directory `{$directory}` ".($this->isDryRun ? 'found' : 'has been removed'));
        });
    }
}
