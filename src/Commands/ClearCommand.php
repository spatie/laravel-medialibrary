<?php

namespace Spatie\MediaLibrary\Commands;

use Spatie\MediaLibrary\Media;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Spatie\MediaLibrary\MediaRepository;
use Illuminate\Database\Eloquent\Collection;

class ClearCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'medialibrary:clear {modelType?} {collectionName?}
    {-- force : Force the operation to run when in production}';

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
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaRepository $mediaRepository)
    {
        parent::__construct();
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Handle command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->getMediaItems()->each(function (Media $media) {
            $media->delete();
        });

        $this->info('All done!');
    }

    public function getMediaItems() : Collection
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
}
