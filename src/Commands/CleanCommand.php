<?php

namespace Spatie\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class CleanCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'medialibrary:clean {modelType?} {collectionName?} {disk?} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean deprecated conversions and files without related model.';

    /**
     * @var \Spatie\MediaLibrary\MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var \Spatie\MediaLibrary\FileManipulator
     */
    protected $fileManipulator;

    /**
     * @var Factory
     */
    private $fileSystem;

    /**
     * @var BasePathGenerator
     */
    private $basePathGenerator;

    /**
     * @var bool
     */
    protected $dry = false;

    /**
     * @param MediaRepository   $mediaRepository
     * @param FileManipulator   $fileManipulator
     * @param Factory           $fileSystem
     * @param BasePathGenerator $basePathGenerator
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

    /**
     * Handle command.
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->dry = $this->option('dry-run');

        // Clean deprecated conversions
        $this->getMediaItems()->each(function (Media $media) {
            $this->cleanDeprecatedConversions($media);
        });

        // Clean files without related models
        $this->cleanOrphanFiles();

        $this->info('All done!');
    }

    public function getMediaItems() : Collection
    {
        $modelType = $this->argument('modelType');
        $collectionName = $this->argument('collectionName');

        if (!is_null($modelType) && !is_null($collectionName)) {
            return $this->mediaRepository->getByModelTypeAndCollectionName(
                $modelType,
                $collectionName
            );
        }

        if (!is_null($modelType)) {
            return $this->mediaRepository->getByModelType($modelType);
        }

        if (!is_null($collectionName)) {
            return $this->mediaRepository->getByCollectionName($collectionName);
        }

        return $this->mediaRepository->all();
    }

    /**
     * @param Media $media
     */
    private function cleanDeprecatedConversions(Media $media)
    {
        // Get conversion directory
        $path = $this->basePathGenerator->getPathForConversions($media);
        $files = $this->fileSystem->disk($media->disk)->files($path);

        // Get the list of currently defined conversions
        $conversions = ConversionCollection::createForMedia($media)->getConversionsFiles($media->collection_name);

        // Verify that each file on disk is defined in a conversion, else we delete the file
        foreach ($files as $file) {
            if (!$conversions->contains(basename($file))) {
                if (!$this->dry) {
                    $this->fileSystem->disk($media->disk)->delete($file);
                }

                $this->info("Deprecated conversion file $file " . ($this->dry ? '' : 'has been removed'));
            }
        }
    }

    private function cleanOrphanFiles()
    {
        $diskName = $this->argument('disk') ?: config('laravel-medialibrary.defaultFilesystem');

        if (is_null(config("filesystems.disks.{$diskName}"))) {
            throw FileCannotBeAdded::diskDoesNotExist($diskName);
        }

        $medias = $this->mediaRepository->all()->pluck('id');
        $directories = new Collection($this->fileSystem->disk($diskName)->directories());

        // Ignore all directories related to a media row and non numeric directories name
        $directories = $directories->filter(function ($directory) use ($medias) {
            return is_numeric($directory) ? !$medias->contains((int)$directory) : false;
        });

        // Delete all directories with bo media row
        foreach ($directories as $directory) {
            if (!$this->dry) {
                $this->fileSystem->disk($diskName)->deleteDirectory($directory);
            }

            $this->info("Orphan media file $directory " . ($this->dry ? '' : 'has been removed'));
        }
    }
}
