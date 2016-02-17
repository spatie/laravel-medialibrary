<?php

namespace Spatie\MediaLibrary\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\MediaRepository;

class ClearCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'medialibrary:clear {modelType?} {collectionName?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all items in a media collection.';

    /**
     * @var \Spatie\MediaLibrary\MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var \Spatie\MediaLibrary\FileManipulator
     */
    protected $fileManipulator;

    /**
     * @param MediaRepository $mediaRepository
     * @param FileManipulator $fileManipulator
     */
    public function __construct(MediaRepository $mediaRepository, FileManipulator $fileManipulator)
    {
        parent::__construct();
        $this->mediaRepository = $mediaRepository;
        $this->fileManipulator = $fileManipulator;
    }

    /**
     * Handle command.
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->getMediaItems()->each(function (Media $media) {
            $media->delete();
        });

        $this->info('All done!');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMediaItems()
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
}
